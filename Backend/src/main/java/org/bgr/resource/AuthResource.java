package org.bgr.resource;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.mailer.Mail;
import io.quarkus.mailer.Mailer;
import io.quarkus.qute.Template;
import net.bytebuddy.utility.RandomString;
import org.bgr.helper.ParserHelper;
import org.bgr.helper.ResultCommonObject;
import org.bgr.helper.ResultHelper;
import org.bgr.model.UserAddressModel;
import org.bgr.model.UserSepaPermissionModel;
import org.bgr.model.api.AuthLoginModel;
import org.bgr.model.api.AuthRegisterModel;
import org.bgr.model.AuthUserVerify;
import org.bgr.model.api.AuthVerifyModel;
import org.bgr.model.db.ActivityModel;
import org.bgr.model.db.FileContainerModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.bgr.model.db.UserDataStatus;
import org.bgr.service.ActivityService;
import org.bgr.service.KeycloakService;

import org.bgr.service.MailService;
import org.bgr.service.UserService;
import org.jboss.resteasy.annotations.jaxrs.PathParam;

import javax.annotation.security.PermitAll;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.json.Json;
import javax.persistence.EntityManager;
import javax.transaction.Transactional;
import javax.validation.ConstraintViolation;
import javax.validation.Validator;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import java.math.BigInteger;
import java.net.http.HttpResponse;
import java.util.*;
import java.util.stream.Collectors;
import org.keycloak.representations.idm.UserRepresentation;

@ApplicationScoped
@Path("auth")
public class AuthResource {
    @Inject
    Validator validator;

    @Inject
    ParserHelper parserHelper;

    @Inject
    KeycloakService keycloakService;

    @Inject
    ActivityService activityService;

    @Inject
    MailService mailService;

    @Inject
    UserService userService;

    @POST
    @Path("register")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultHelper register(AuthRegisterModel registerData) {

        System.out.println("here 1");

        Set<ConstraintViolation<AuthRegisterModel>> violations = validator.validate(registerData);
        if (violations.isEmpty()) {

            System.out.println("here 2");

            //create keycloak user
            UserRepresentation keycloakUser = new UserRepresentation();
            keycloakUser.setFirstName(registerData.firstName);
            keycloakUser.setLastName(registerData.lastName);
            keycloakUser.setEmail(registerData.username);
            keycloakUser.setEmailVerified(false);
            keycloakUser.setEnabled(false);
            keycloakUser.setAttributes(Collections.singletonMap("origin", Arrays.asList("web-user")));

            //try to register user in keycloak
            ResultCommonObject res = keycloakService.register(keycloakUser);
            if (res.getStatus() != 200) {
                System.out.println("here 3");
                return new ResultHelper(400, res);
            }

            System.out.println("here 4");

            //if user created in keycloak, make insert in db -- this could be moved to service
            UUID newUserId = UUID.fromString(res.getMessage());

            //reset user password
            try {
                keycloakService.resetPassword(registerData.password, res.getMessage());
                keycloakService.addUserToRole(res.getMessage());
                System.out.println("here 5");
            } catch (Exception e) {
                //skip on error for now -- there is frontend password reset function
                System.out.println("here 6");
            }

            UserBasicInfoModel user = new UserBasicInfoModel();
            user.userId = newUserId;
            user.email = registerData.username.toLowerCase();
            user.firstName = registerData.firstName;
            user.lastName = registerData.lastName;

            //persist user
            try{
                user.persist();

                //temp for demo
                UserDataStatus userStatus = new UserDataStatus();
                userStatus.userId = newUserId;
                userStatus.persist();

                //Activity example
                /*
                ActivityModel activity = new ActivityModel();
                activity.title = "User Created";
                activity.content = "User registered trough web application";
                activity.userId = user.id;
                activity.contentType = "user";
                activity.contentId = user.id;
                activity.showOnUserDashboard = true;
                activity.notificationType = "info";
                activity.rs = 0;
                activity.persist();
                */

                ActivityModel activity = new ActivityModel();
                activity.userId = user.id;
                activity.contentId = user.id;

                activityService.processActivity(activity, "user_register_web");

                //temp create user file_containers
                //A_H-solar.family ausweisbild
                FileContainerModel fc = new FileContainerModel();
                fc.contextType = 1;
                fc.type = 11;
                fc.contextId = newUserId;
                fc.rs = 1;
                fc.sortOrder = 1;
                fc.uploadOnly = true;
                fc.downloadOnly = false;
                fc.backendUploadOnly = false;
                fc.backendOnly = false;
                fc.noStatusUpdate = false;
                fc.persist();

                /*
                //A_G-solar.family Vollmacht Energieabrechnung
                fc = new FileContainerModel();
                fc.contextType = 1;
                fc.type = 13;
                fc.contextId = newUserId;
                fc.rs = 1;
                fc.uploadOnly = false;
                fc.downloadOnly = false;
                fc.backendUploadOnly = false;
                fc.backendOnly = false;
                fc.noStatusUpdate = false;
                fc.persist();
                */

                //A_H-solar.family SEPA Lastschriftmandat
                fc = new FileContainerModel();
                fc.contextType = 1;
                fc.type = 14;
                fc.contextId = newUserId;
                fc.rs = 1;
                fc.sortOrder = 2;
                fc.uploadOnly = false;
                fc.downloadOnly = false;
                fc.backendUploadOnly = false;
                fc.backendOnly = false;
                fc.noStatusUpdate = false;
                fc.persist();

                /*
                UserAddressModel address = new UserAddressModel();
                address.userId = newUserId;
                address.persist();

                //user powerbill placeholder
                UserPowerBill powerBill = new UserPowerBill();
                powerBill.userId = newUserId;
                powerBill.rs = 1;
                powerBill.persist();

                //user sepa
                UserSepaPermissionModel sepa = new UserSepaPermissionModel();
                sepa.userId = newUserId;
                sepa.rs = 1;
                sepa.persist();
                 */

                UserBasicInfoModel ubm = new UserBasicInfoModel();
                EntityManager em = ubm.getEntityManager();

                //address
                String q = new StringBuilder()
                    .append("INSERT INTO user_address(id, userId) ")
                    .append("VALUES ('"+UUID.randomUUID().toString()+"','"+newUserId+"') ")
                    .toString();

                em.createNativeQuery(q).executeUpdate();

                /*
                //power bill
                q = new StringBuilder()
                    .append("INSERT INTO user_power_bill(id, userId, rs) ")
                    .append("VALUES ('"+UUID.randomUUID().toString()+"','"+newUserId+"', 1) ")
                    .toString();

                em.createNativeQuery(q).executeUpdate();
                */

                //sepa
                q = new StringBuilder()
                        .append("INSERT INTO user_sepa_permission(id, userId, rs) ")
                        .append("VALUES ('"+UUID.randomUUID().toString()+"','"+newUserId+"', 1) ")
                        .toString();

                em.createNativeQuery(q).executeUpdate();

            } catch (Exception e) {
                System.out.println("here 8");
                return new ResultHelper(400, new ResultCommonObject(400, e.toString()));
            }

            //skip error on verification table.
            System.out.println("here 9");
            ResultCommonObject verificationData = userService.addUserToVerificationTable(user, "verify-email");
            System.out.println("here 10");

            //send mail
            if (verificationData.getStatus() == 200) {
                System.out.println("here 11");
                mailService.sendWebRegisterMail(user.email, verificationData.getMessage(), newUserId.toString());
            }
            //else do error

            System.out.println("here 12");

            return new ResultHelper(new ResultCommonObject(200, "User Created"));
        } else {
            return new ResultHelper(new ResultCommonObject(400, "Register data not valid"));
        }
    }

    //ok
    @POST
    @Path("login")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultHelper login(AuthLoginModel authData) {
        return keycloakService.loginKeycloakFrontend(authData);
    }

    //ok
    @POST
    @PermitAll
    @Path("refresh-token")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultHelper refreshToken(JsonNode token) {
        return keycloakService.validateRefreshToken(token.get("refreshToken").asText());
    }

    //ok
    @POST
    @Path("logout")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultCommonObject logout(JsonNode token) {
        System.out.println("------------------------------LOGOUT--------------------------");
        System.out.println(token);

        keycloakService.logout(token.get("refreshToken").asText());

        return new ResultCommonObject(200, "OK");
    }

    //ok
    @POST
    @Path("password-reset")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultHelper passwordReset(AuthVerifyModel verifyData) {

        Map<String, Object> params = new HashMap<>();
        params.put("userId", verifyData.userId);
        params.put("token", verifyData.token);
        params.put("rs", 0);
        params.put("type", "password-reset");

        AuthUserVerify verify = AuthUserVerify.find("userId = :userId and token = :token and rs = :rs and type = :type", params).firstResult();

        if (verify != null) {
            try{
                //update password on keycloak server
                keycloakService.resetPassword(verifyData.data, verify.userId.toString());

                //update record in authUserVerify table
                verify.rs = 1;
                verify.persist();

                //send email to user
                mailService.sendPasswordResetSuccessMail(verify.email);

                //send response to client
                return new ResultHelper( new ResultCommonObject(200,"Ok"));
            } catch (Exception e) {
                return new ResultHelper( new ResultCommonObject(500,"Error updating password on server"));
            }
        } else {
            //error
            return new ResultHelper( new ResultCommonObject(400,"Token not found"));
        }
    }

    @POST
    @Path("verify-account")
    @Transactional
    public ResultHelper verifyAccount(AuthVerifyModel verifyData) {

        Map<String, Object> params = new HashMap<>();
        params.put("userId", verifyData.userId);
        params.put("token", verifyData.token);
        params.put("rs", 0);
        params.put("type", "verify-email");

        AuthUserVerify verify = AuthUserVerify.find("userId = :userId and token = :token and rs = :rs and type = :type", params).firstResult();


        if (verify != null) {
            try{
                //update password on keycloak server
                keycloakService.verifyAndEnableAccount(verify.userId.toString());

                //update record in authUserVerify table
                verify.rs = 1;
                verify.persist();

                //send email to user
                mailService.sendUserVerifySuccessMail(verify.email);

                //send response to client
                return new ResultHelper( new ResultCommonObject(200,"Ok"));
            } catch (Exception e) {
                return new ResultHelper( new ResultCommonObject(500,"Error enabling web user"));
            }
        } else {
            //error
            return new ResultHelper( new ResultCommonObject(400,"Token not found"));
        }
    }

    //ok
    @POST
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Path("password-change-request")
    @Transactional
    public ResultHelper passwordChangeRequest(JsonNode email) {
        try {
            UserBasicInfoModel user = UserBasicInfoModel.find("email", email.get("email").asText()).firstResult();
            if (user != null) {
                System.out.println(user.userId);
                //skip error on verification table.
                ResultCommonObject verificationData = userService.addUserToVerificationTable(user, "password-reset");

                //send mail
                if (verificationData.getStatus() == 200) {
                    mailService.sendForgetPasswordRequest(user.email, verificationData.getMessage(), user.userId.toString());
                }

                return new ResultHelper(new ResultCommonObject(200, "OK"));
            } else {
                return new ResultHelper(new ResultCommonObject(400, "User Not Found"));
            }
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(500, "Server Error"));
        }
    }
}
