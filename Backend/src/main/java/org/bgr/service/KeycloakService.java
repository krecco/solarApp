package org.bgr.service;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;
import net.bytebuddy.utility.RandomString;
import org.apache.http.impl.client.DefaultHttpClient;
import org.bgr.helper.MessagingBackendUser;
import org.bgr.helper.ResultCommonObject;
import org.bgr.helper.ResultHelper;
import org.bgr.model.UserAddressModel;
import org.bgr.model.api.AuthLoginModel;
import org.bgr.model.api.AuthRegisterModel;
import org.bgr.model.AuthUserVerify;
import org.bgr.model.db.FileContainerModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.eclipse.microprofile.config.inject.ConfigProperty;
import org.eclipse.microprofile.rest.client.inject.RegisterRestClient;
import org.keycloak.OAuth2Constants;
import org.keycloak.admin.client.CreatedResponseUtil;
import org.keycloak.admin.client.Keycloak;
import org.keycloak.admin.client.KeycloakBuilder;
import org.keycloak.admin.client.resource.RealmResource;
import org.keycloak.admin.client.resource.UserResource;
import org.keycloak.admin.client.resource.UsersResource;
import org.keycloak.representations.idm.CredentialRepresentation;
import org.keycloak.representations.idm.RoleRepresentation;
import org.keycloak.representations.idm.UserRepresentation;

import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule;

import javax.enterprise.context.ApplicationScoped;
import javax.json.Json;
import javax.transaction.Transactional;
import javax.ws.rs.core.Response;
import java.io.UnsupportedEncodingException;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.*;

@ApplicationScoped
public class KeycloakService {

    @ConfigProperty(name = "app.keycloak.url")
    String keycloakUrl;

    @ConfigProperty(name = "app.keycloak.realm")
    String keyCloakRealm;

    @ConfigProperty(name = "quarkus.oidc.client-id")
    String keyCloakClient;

    @ConfigProperty(name = "app.keycloak.client-secret")
    String keyCloakClientSecret;

    @ConfigProperty(name = "app.keycloak.adminId")
    String adminId;

    @ConfigProperty(name = "app.keycloak.adminSecret")
    String adminSecret;



    public RealmResource getKeycloak() throws Exception {
        Keycloak keycloak = KeycloakBuilder.builder()
                .serverUrl(keycloakUrl)
                .realm(keyCloakRealm)
                .clientId(adminId)
                .clientSecret(adminSecret)
                .grantType(OAuth2Constants.CLIENT_CREDENTIALS)
                .build();

        return keycloak.realm(keyCloakRealm);
    }

    public ResultCommonObject register(UserRepresentation keycloakUser) {
        try {
            RealmResource keycloakRealm = getKeycloak();
            try {
                UsersResource usersResource = keycloakRealm.users();

                Response response = usersResource.create(keycloakUser);
                String userId = CreatedResponseUtil.getCreatedId(response);

                response.close();

                return new ResultCommonObject(200, userId);
            } catch (Exception e) {
                return new ResultCommonObject(400, e.getMessage());
            }
        } catch (Exception e) {
            return new ResultCommonObject(401, "Please check your oauth credentials!");
        }
    }

    public String resetPassword(String password, String userId) throws Exception {
        RealmResource keycloakRealm = getKeycloak();

        try {
            CredentialRepresentation passwordCred = new CredentialRepresentation();
            passwordCred.setTemporary(false);
            passwordCred.setUserLabel("Registration Password");
            passwordCred.setType(CredentialRepresentation.PASSWORD);
            passwordCred.setValue(password);

            UsersResource usersResource = keycloakRealm.users();
            UserResource userResource = usersResource.get(userId);

            // Set password credential
            userResource.resetPassword(passwordCred);
        } catch (Exception e) {
            System.out.println("this is error");
            System.out.println(e);
        }

        return "OK";
    }

    public void addUserToRole(String userId) throws Exception {
        RealmResource keycloakRealm = getKeycloak();

        try {
            UsersResource usersResource = keycloakRealm.users();
            UserResource userResource = usersResource.get(userId);

            RoleRepresentation userRealmRole = keycloakRealm.roles().get("user").toRepresentation();
            userResource.roles().realmLevel().add(Arrays.asList(userRealmRole));
        } catch (Exception e) {
            System.out.println("this is error");
            System.out.println(e);
        }
    }

    public String verifyAndEnableAccount(String userId) throws Exception {
        RealmResource keycloakRealm = getKeycloak();

        try {
            UsersResource usersResource = keycloakRealm.users();
            UserResource userResource = usersResource.get(userId);

            UserRepresentation user = userResource.toRepresentation();
            user.setEmailVerified(true);
            user.setEnabled(true);

            /* does not work
            List<String> realmRoleNames = new ArrayList<>();
            realmRoleNames.add(userRealmRole.getName());
            user.setRealmRoles(realmRoleNames);
            */

            /* works
            RoleRepresentation userRealmRole = keycloakRealm.roles().get("user").toRepresentation();
            userResource.roles().realmLevel().add(Arrays.asList(userRealmRole));
            */

            userResource.update(user);
        } catch (Exception e) {
            System.out.println("this is error");
            System.out.println(e);
        }

        return "OK";
    }

    public ResultHelper loginKeycloakFrontend(AuthLoginModel authData) {
        try {
            Keycloak keycloak = KeycloakBuilder.builder()
                    .serverUrl(keycloakUrl)
                    .realm(keyCloakRealm)
                    .clientId(keyCloakClient)
                    .clientSecret(keyCloakClientSecret)
                    .username(authData.username)
                    .password(authData.password)
                    .grantType(OAuth2Constants.PASSWORD)
                    .build();

            ObjectMapper mapper = new ObjectMapper();
            ObjectNode node = mapper.createObjectNode();

            node.put("accessToken", keycloak.tokenManager().getAccessToken().getToken());
            node.put("refreshToken", keycloak.tokenManager().refreshToken().getRefreshToken());

            UserBasicInfoModel user = UserBasicInfoModel.find("email", authData.username.toLowerCase()).firstResult();
            if (user != null) {
                node.put("uid", user.id.toString());
                node.put("firstName", user.firstName);
                node.put("lastName", user.lastName);
                //node.put("chatId", user.id.toString());
            } else {
                node.put("uid", "backend_user");
                //node.put("chatId", MessagingBackendUser.getMessagingBackendUser().id.toString());
            }

            return new ResultHelper(200, node);
        }
        catch(Exception e) {
            System.out.println(e);
            return new ResultHelper(new ResultCommonObject(400, e.getMessage()));
        }
    }

    private String getDataString(HashMap<String, String> params) throws UnsupportedEncodingException {
        StringBuilder result = new StringBuilder();
        boolean first = true;
        for(Map.Entry<String, String> entry : params.entrySet()){
            if (first)
                first = false;
            else
                result.append("&");
            result.append(URLEncoder.encode(entry.getKey(), "UTF-8"));
            result.append("=");
            result.append(URLEncoder.encode(entry.getValue(), "UTF-8"));
        }
        return result.toString();
    }

    public ResultHelper validateRefreshToken(String token) {
        try {
            var values = new HashMap<String, String>() {{
                put("client_id", keyCloakClient);
                put("client_secret", keyCloakClientSecret);
                put("grant_type", "refresh_token");
                put ("refresh_token", token);
            }};

            System.out.println(getDataString(values));

            HttpClient client = HttpClient.newHttpClient();
            HttpRequest request = HttpRequest.newBuilder()
                    .header("Content-Type", "application/x-www-form-urlencoded")
                    .uri(URI.create(keycloakUrl+"realms/"+keyCloakRealm+"/protocol/openid-connect/token"))
                    .POST(HttpRequest.BodyPublishers.ofString(getDataString(values)))
                    .build();

            HttpResponse<String> response = client.send(request,
                    HttpResponse.BodyHandlers.ofString());

            /*
            System.out.println(response.statusCode());
            System.out.println(response.body());
            */

            JsonNode jnode = new ObjectMapper().readTree(response.body());

            /*
            System.out.println(jnode);
            System.out.println(jnode.get("access_token"));
             */

            if (jnode.get("access_token") != null) {
                ObjectNode node = new ObjectMapper().createObjectNode();
                node.set("accessToken", jnode.get("access_token"));
                return new ResultHelper(200, node);
            } else {
                return new ResultHelper(401, new ResultCommonObject(401, "Refresh token not valid"));
            }
        } catch (Exception e) {
            return new ResultHelper(401, new ResultCommonObject(401, "Refresh token not valid"));
        }
    }

    public void logout(String token) {
        try {
            var values = new HashMap<String, String>() {{
                put("client_id", keyCloakClient);
                put("client_secret", keyCloakClientSecret);
                put("grant_type", "refresh_token");
                put ("refresh_token", token);
            }};

            HttpClient client = HttpClient.newHttpClient();
            HttpRequest request = HttpRequest.newBuilder()
                    .header("Content-Type", "application/x-www-form-urlencoded")
                    .uri(URI.create(keycloakUrl+"realms/"+keyCloakRealm+"/protocol/openid-connect/logout"))
                    .POST(HttpRequest.BodyPublishers.ofString(getDataString(values)))
                    .build();

            HttpResponse<String> response = client.send(request,
                    HttpResponse.BodyHandlers.ofString());

            System.out.println(response.statusCode());
            System.out.println(response.body());

        } catch (Exception e) {
            System.out.println(e);
        }
    }
}
