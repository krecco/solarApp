package org.bgr.resource;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ArrayNode;
import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.panache.common.Page;
import io.quarkus.panache.common.Sort;
import org.apache.commons.io.FileUtils;
import org.bgr.helper.FileHelper;
import org.bgr.helper.ResultCommonObject;
import org.bgr.helper.ParserHelper;
import org.bgr.helper.ResultHelper;
import org.bgr.model.*;

import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.persistence.EntityManager;
import javax.persistence.OrderBy;
import javax.persistence.criteria.Order;
import javax.transaction.Transactional;
import javax.validation.*;
import javax.ws.rs.*;
import javax.ws.rs.Path;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.MultivaluedMap;
import javax.ws.rs.core.Response;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.math.BigInteger;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.*;

import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.model.api.UserListModel;
import org.bgr.model.db.*;
import org.bgr.service.*;
import org.eclipse.microprofile.config.inject.ConfigProperty;
import org.eclipse.microprofile.jwt.JsonWebToken;
import org.jboss.resteasy.plugins.providers.multipart.InputPart;
import org.jboss.resteasy.plugins.providers.multipart.MultipartFormDataInput;

import org.apache.commons.io.IOUtils;
import org.keycloak.representations.idm.UserRepresentation;

@ApplicationScoped
@Path("user")
public class UserResource {

    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @Inject
    KeycloakService keycloakService;

    @Inject
    UserService userService;

    @Inject
    ActivityService activityService;

    @Inject
    UserAddressService userAddressService;

    @Inject
    UserSepaPermissionService userSepaPermissionService;

    @Inject
    SolarPlantPowerBillService solarPlantPowerBillService;

    @Inject
    InvestmentService investmentService;

    @Inject
    MailService mailService;

    @Inject
    ParserHelper parserHelper;

    @Inject
    FileHelper fileHelper;

    @ConfigProperty(name = "app.folder.uploads")
    String uploadsFolder;

    @ConfigProperty(name = "app.folder.generated")
    String generatedFilesFolder;

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Produces("application/json")
    @Path("list")
    public UserResult getUsers(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy, @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("customerType") Integer customerType) {
        try{
            return new UserResult(200,
                    UserBasicInfoModel.countUsers(q, customerType),
                    UserBasicInfoModel.listUsers(sortBy, descending, page, perPage, q, customerType)
            );
        } catch (Exception e) {
            System.out.println(e);
            return (UserResult) new ResultHelper(400,new ResultCommonObject(400, "Error parsing json"));
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("delete-user/{userId}")
    @Transactional
    public ResultCommonObject deleteUser(@PathParam("userId") UUID userId) {
        try {
            //do we need a condition if we can do storno?

            //storno plant
            List<ProjectUserModel> projectList = ProjectUserModel.find("userId", userId).list();

            for (ProjectUserModel project : projectList) {
                SolarPlantModel.update("rs=99 where id=?1", project.plantId);
            }

            //storno investment
            InvestmentModel.update("rs=99 where userId=?1", userId);

            UserBasicInfoModel user = UserBasicInfoModel.findById(userId);
            user.rs = 99;
            user.persist();

            return new ResultCommonObject(200, "OK");
        } catch (Exception e) {
            return new ResultCommonObject(500, "FAIL");
        }
    }

    //OK - 3
    /*
    @GET
    @RolesAllowed({"manager", "admin"})
    @Produces("application/json")
    @Path("chat-list")
    public UserResult getChatUsers(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy, @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("customerType") Integer customerType) {
        try{
            return new UserResult(200, UserBasicInfoModel.count(), UserBasicInfoModel.listUsers(sortBy, descending, page, perPage, q, customerType));
        } catch (Exception e) {
            System.out.println(e);
            return (UserResult) new ResultHelper(400,new ResultCommonObject(400, "Error parsing json"));
        }
    }
    */

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Produces("application/json")
    @Path("chat-list")
    public ArrayNode getChatUsers(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy, @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("customerType") Integer customerType) {
        try{
            ObjectMapper mapper = new ObjectMapper();
            ArrayNode chatUsers = mapper.createArrayNode();

            List<UserListModel> users = UserBasicInfoModel.listUsers(sortBy, descending, page, perPage, q, customerType);
            for (UserListModel user : users) {
                ObjectNode chatUser = mapper.createObjectNode();

                //chatUser.put("id", user.id.toString());
                chatUser.put("userId", user.userId.toString());
                chatUser.put("id", user.id.toString());
                chatUser.put("fullName", user.lastName+" "+user.firstName );
                chatUser.put("avatar", user.lastName.substring(0,1)+""+user.firstName.substring(0,1));
                chatUser.put("avatarBg", "success");

                chatUsers.add(chatUser);
            }

            return chatUsers;
            //return new UserResult(200, UserBasicInfoModel.count(), UserBasicInfoModel.listUsers(sortBy, descending, page, perPage, q, customerType));
        } catch (Exception e) {
            ObjectMapper mapper = new ObjectMapper();
            ArrayNode errors = mapper.createArrayNode();
            ObjectNode error = mapper.createObjectNode();
            error.put("error", e.toString());
            errors.add(error);

            return errors;
        }
    }

    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Produces("application/json")
    @Path("basic-info/{userId}")
    public ResultHelper getUserInfo(@PathParam("userId") UUID userId) {
        try {
            UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

            if (user == null) {
                return new UserResult(404, user);
            } else {

                if(securityIdentity.getRoles().contains("user")) {
                    if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                        return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                    }
                }

                return new UserResult(200, user);
            }
        } catch (Exception e) {
            return new ResultHelper(500, new ResultCommonObject(500, e.getMessage()));
        }
    }

    /*
    * Add new user from backend interface
    * */
    //OK - 3
    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("add-user-backend")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultHelper addUserBackend(UserBasicInfoModel user) {

        try {
            userService.validateUserBasicInfo(user);

            /*
            List<String> roleList = new ArrayList<>();
            roleList.add("user");
            */

            user.email = user.email.toLowerCase().trim();
            user.firstName = user.firstName.trim();
            user.lastName = user.lastName.trim();

            //create keycloak user
            UserRepresentation keycloakUser = new UserRepresentation();
            keycloakUser.setFirstName(user.firstName);
            keycloakUser.setLastName(user.lastName);
            keycloakUser.setEmail(user.email);
            keycloakUser.setEmailVerified(true);
            keycloakUser.setEnabled(true);
            //keycloakUser.setRealmRoles(roleList);

            keycloakUser.setAttributes(Collections.singletonMap("origin", Arrays.asList("backend-added-user")));

            //try to register user in keycloak
            ResultCommonObject res = keycloakService.register(keycloakUser);

            if (res.getStatus() != 200) {
                return new ResultHelper(400, res);
            }

            //if user created in keycloak, make insert in db -- this could be moved to service
            UUID newUserId = UUID.fromString(res.getMessage());
            user.userId = newUserId;

            //persist user
            try{
                //add user role
                keycloakService.addUserToRole(res.getMessage());

                user.persist();

                //this code will be moved in optimize phase -- we are always in a hurry !!
                //temp for demo

                UserDataStatus userStatus = new UserDataStatus();
                userStatus.userId = newUserId;
                userStatus.persist();

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
                /*
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
                 */

                /*
                //address
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

                //power bill
                /*
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

                ActivityModel activity = new ActivityModel();
                activity.userId = user.id;
                activity.contentId = user.id;

                activityService.processActivity(activity, "user_register_backend");
            } catch (Exception e) {
                return new ResultHelper(400, new ResultCommonObject(400, e.toString()));
            }

            //skip error on verification table.
             ResultCommonObject verificationData = userService.addUserToVerificationTable(user, "password-reset");

            //send mail
            // solar.familt - do not send mail
            /*
            if (verificationData.getStatus() == 200) {
                mailService.sendBackendRegisterMail(verificationData.getMessage(), newUserId.toString());
            } else {
                return new ResultHelper(500, new ResultCommonObject(500, "Problem connecting to SMTP server"));
            }
             */

            return new UserResult(200, user);
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    //OK - 3
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Path("basic-info-edit")
    @Transactional
    public ResultHelper updateUserInfo(UserBasicInfoModel user) {
        try{
            userService.validateUserBasicInfo(user);

            user.email = user.email.toLowerCase().trim();
            user.firstName = user.firstName.trim();
            user.lastName = user.lastName.trim();

            //this should be optimized in next phase
            UserBasicInfoModel u = UserBasicInfoModel.find("id", user.id).firstResult();
            if(securityIdentity.getRoles().contains("user")) {
                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            return new ResultHelper(200, userService.updateUser(user));
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("address/{userId}")
    public ResultHelper getUserAddress(@PathParam("userId") UUID userId) {

        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
        return new UserResult(200, address);
    }

    //OK - 3
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Path("address/{userId}")
    @Transactional
    public ResultHelper addUserAddress(@PathParam("userId") UUID userId, UserAddressModel addressRq) {

        try{
            userAddressService.validateUserAddress(addressRq);

            UserAddressModel address = UserAddressModel.find("userId", userId).firstResult();
            if(securityIdentity.getRoles().contains("user")) {
                if (address.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            address.city = addressRq.city;
            address.street = addressRq.street;
            address.postNr = addressRq.postNr;
            address.addressExtra = addressRq.addressExtra;
            address.persist();

            userService.checkUserAddress(address.id);

            return new ResultHelper(200, new ResultCommonObject(200, "User Address Updated"));
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    //OK - 3
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Path("upload-multipart-file/{containerId}")
    @Consumes(MediaType.MULTIPART_FORM_DATA)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultCommonObject uploadMultipart (MultipartFormDataInput input, @PathParam("containerId") UUID containerId) throws Exception {

        System.out.println("----------------------------------------------------------------------------------------------------");
        System.out.println("containerId");
        System.out.println(containerId);

        //rewrite code in optimizing phase // we have no time at the moment for hql
        FileContainerModel fc = FileContainerModel.findById(containerId);

        Boolean backendUserUpload = true;

        //todo update this for contextType 4 -- if needed for frontend user
        if (securityIdentity.getRoles().contains("user")) {
            backendUserUpload = false;
            if (fc.contextType == 1) {
                if (fc.contextId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            } else if ((fc.contextType == 2) ||  (fc.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            } else {
                return new ResultCommonObject(404, "Context type does not exists");
            }
        }


        String fileName = "";
        String fileType = "";

        Map<String, List<InputPart>> uploadForm = input.getFormDataMap();
        List<InputPart> inputParts = uploadForm.get("fileUpload");
        String folder = uploadsFolder;

        for (InputPart inputPart : inputParts) {
            try {

                MultivaluedMap<String, String> header = inputPart.getHeaders();

                fileName = fileHelper.getFileName(header).replaceAll(" ", "_");
                fileType = fileHelper.getFileType(header);

                if (!fileName.equals("unknown")) {
                    //convert the uploaded file to inputstream
                    InputStream inputStream = inputPart.getBody(InputStream.class,null);

                    byte [] bytes = IOUtils.toByteArray(inputStream);

                    System.out.println(fileName);

                    String referenceFile = fileName;

                    //constructs upload file path
                    fileName = folder + fileName;

                    fileHelper.writeFile(bytes,fileName);

                    System.out.println("Done");

                    FileModel file = new FileModel();
                    file.fileName = referenceFile;
                    file.fileContentType = fileType;
                    file.filePath = folder;
                    file.idFileContainer = containerId;
                    file.rs = 1;
                    file.backendUserUpload = backendUserUpload;
                    file.persist();

                    ActivityModel activity = new ActivityModel();
                    //FileContainerModel fcm = FileContainerModel.findById(containerId);
                    if (fc.rs == 4) {
                        fc.rs = 3;
                        activity.title = "Modified";
                        activity.content = "File has been Modified";
                    } else {
                        fc.rs = 2;
                        activity.title = "Uploaded";
                        activity.content = "File has been Uploaded";
                    }
                    fc.persist();

                    if (fc.contextType == 1) {
                        UserBasicInfoModel u = UserBasicInfoModel.find("userId", fc.contextId).firstResult();
                        activity.userId = u.id;
                    } else if ((fc.contextType == 2) || (fc.contextType == 3)) {
                        ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                        activity.parentContentId = fc.contextId;
                        activity.userId = pu.userId;
                    } else if (fc.contextType == 4) {
                        InvestmentModel im = InvestmentModel.findById(fc.contextId);
                        activity.parentContentId = fc.contextId;
                        activity.userId = im.userId;
                    }

                    /*
                    activity.showOnUserDashboard = false;
                    activity.contentType = contentType;
                    activity.notificationType = "info";
                    activity.contentId = containerId;
                    //activity.userId = UUID.fromString(token.getSubject());

                    activity.persist();
                    return new ResultCommonObject(fc.rs, containerId.toString());
                    */

                    activity.contentId = containerId;
                    activity.filename = referenceFile;
                    activityService.processActivity(activity, "file_upload");

                    return new ResultCommonObject(fc.rs, containerId.toString());

                }
            } catch (IOException e) {
                e.printStackTrace();
                return new ResultCommonObject(401, "Fail");
                //return "2";
            }
        }

        //return new ResultHelper(new ResultCommonObject(401, "Fail"));
        return new ResultCommonObject(401, "Fail");
    }

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-document-status/{id}/{status}")
    @Transactional
    public ResultHelper updateDocumentStatus(@PathParam("id") UUID id, @PathParam("status") Integer status) {
        try {
            FileContainerModel fcm = FileContainerModel.findById(id);
            fcm.rs = status;
            fcm.persist();

            ActivityModel activity = new ActivityModel();
            if (fcm.contextType == 1) {
                UserBasicInfoModel u = UserBasicInfoModel.find("userId", fcm.contextId).firstResult();
                activity.userId = u.id;
            }
            else if ((fcm.contextType == 2) || (fcm.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fcm.contextId).firstResult();
                activity.parentContentId = fcm.contextId;
                activity.userId = pu.userId;
            }
            else if (fcm.contextType == 4) {
                InvestmentModel im = InvestmentModel.findById(fcm.contextId);
                activity.parentContentId = fcm.contextId;
                activity.userId = im.userId;
            }

            activity.contentId = fcm.id;
            activityService.processActivity(activity, "file_status_change");


            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-user-file-status/{id}/{status}")
    @Transactional
    public ResultHelper updateUserFileStatus(@PathParam("id") UUID id, @PathParam("status") Boolean status) {
        try {
            UserBasicInfoModel user = UserBasicInfoModel.findById(id);
            user.userFilesVerifiedByBackendUser = status;
            user.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("app-activity-status/{id}/{status}")
    @Transactional
    public ResultHelper updateActivityStatus(@PathParam("id") UUID id, @PathParam("status") Integer status) {
        try {
            ActivityModel a = ActivityModel.findById(id);
            a.rs = status;
            a.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"user","manager", "admin"})
    @Path("get-user-files-status/{id}")
    @Transactional
    public ResultHelper getUserFilesStatus(@PathParam("id") UUID id) {
        try {
            UserBasicInfoModel user = UserBasicInfoModel.findById(id);
            String status = "new";

            if (user.userFilesVerifiedByBackendUser == null) {
                user.userFilesVerifiedByBackendUser = false;
            }

            //jupi!
            if (user.userFilesVerifiedByBackendUser == false) {
                System.out.println("status");
                //do count for uploaded files
                String q = new StringBuilder()
                    .append("select count(f.id) as nr ")
                    .append("from file f ")
                    .append("left join file_container fc on fc.id = f.idFileContainer ")
                    .append("where fc.contextId = '"+user.userId+"' ")
                    .toString();

                System.out.println(q);

                UserBasicInfoModel ubm = new UserBasicInfoModel();
                EntityManager em = ubm.getEntityManager();
                List<BigInteger>  results = em.createNativeQuery(q).getResultList();

                System.out.println(q);
                System.out.println(results);

                if (results.get(0).intValue() > 0) {
                    status = "progress";
                }
            } else {
                status = "true";
            }

            return new ResultHelper(new ResultCommonObject(200, status));
        } catch (Exception e) {
            System.out.println(e.toString());
            return new ResultHelper(new ResultCommonObject(400, "Status query failed!"));
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("document-files/{id}")
    @Transactional
    public ResultHelper getDocumentFiles(@PathParam("id") UUID id) {

        //rewrite code in optimizing phase // we have no time at the moment for hql
        FileContainerModel fc = FileContainerModel.findById(id);
        if (securityIdentity.getRoles().contains("user")) {
            if (fc.contextType == 1) {
                if (fc.contextId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }else if ((fc.contextType == 2) || (fc.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            } else {
                return new ResultHelper(new ResultCommonObject(404, "Context type does not exists"));
            }
        }

        List<FileModel> fm = FileModel.find("idFileContainer", Sort.by("t0").descending(), id).list();
        return new FileResult(200, fm);
    }

    /* deprecated
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("file-attachment/{fileId}")
    @Transactional
    public Response getFileAttachment(@PathParam("fileId") UUID fileId) {

        System.out.println(fileId);

        FileModel file = FileModel.findById(fileId);
        System.out.println(file.fileName);

        String path = uploadsFolder;

        File fileDownload = new File(path + file.fileName);
        Response.ResponseBuilder response = Response.ok((Object) fileDownload);
        response.header("Content-Disposition", "attachment;filename=" + file.fileName);
        return response.build();

    }
    */


    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("file/{fileId}")
    @Transactional
    public Response getFile(@PathParam("fileId") UUID fileId) {

        FileModel file = FileModel.findById(fileId);

        Boolean frontendUser = false;

        //rewrite code in optimizing phase // we have no time at the moment for hql
        FileContainerModel fc = FileContainerModel.findById(file.idFileContainer);
        if (securityIdentity.getRoles().contains("user")) {
            frontendUser = true;
            if (fc.contextType == 1) {
                if (fc.contextId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return Response.status(403).build();
                }
            }else if ((fc.contextType == 2) || (fc.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return Response.status(403).build();
                }
            } else {
                return Response.status(404).build();
            }
        }

        if (frontendUser == true) {
            file.downloadedByUser = true;
            file.downloadedByUserDate = LocalDateTime.now();
            file.downloadedByUserId = UUID.fromString(token.getSubject());
        }

        String path = uploadsFolder;

        /*
        if (fc.downloadOnly == true) {
            //only prognoserechnung for now
            path = generatedFilesFolder+"forecast_calculation/";
        }
        */

        if (file.generated != null && file.generated == true) {
            if (fc.type == 21) {
                path = generatedFilesFolder+"forecast_calculation/";
            }
        }

        File fileDownload = new File(path + file.fileName);
        Response.ResponseBuilder response = Response.ok((Object) fileDownload, file.fileContentType);

        response.header("Content-Disposition", "attachment; filename=" + file.fileName);
        return response.build();
    }

    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("file/{fileId}/{dummyFileName}")
    @Transactional
    public Response getFileDummy(@PathParam("fileId") UUID fileId)  {
        return getFile(fileId);
    }

    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("file-base/{fileId}")
    @Transactional
    public Response getFileBtoa(@PathParam("fileId") UUID fileId) {
        FileModel file = FileModel.findById(fileId);

        FileContainerModel fc = FileContainerModel.findById(file.idFileContainer);
        if (securityIdentity.getRoles().contains("user")) {
            if (fc.contextType == 1) {
                if (fc.contextId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return Response.status(403).build();
                }
            }else if ((fc.contextType == 2) || (fc.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return Response.status(403).build();
                }
            } else {
                return Response.status(404).build();
            }
        }

        String path = uploadsFolder;

        if (file.generated != null && file.generated == true) {
            if (fc.type == 21) {
                path = generatedFilesFolder+"forecast_calculation/";
            }
        }

        try {
            File fileDownload = new File(path + file.fileName);
            //Response.ResponseBuilder response = Response.ok((Object) fileDownload, file.fileContentType);
            Response.ResponseBuilder response = Response.ok(Base64.getEncoder().encodeToString(FileUtils.readFileToByteArray(fileDownload)), file.fileContentType);

            response.header("Content-Disposition", "attachment;filename=" + file.fileName);
            return response.build();
        }

        catch (Exception e) {
            System.out.println(e.toString());
            return Response.status(404).build();
        }
    }

    //todo !!! this function returns btoa of backend generated files -- if needed for frontend user add controls !!!
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-generated-files/{id}/{type}")
    @Transactional
    public Response getGeneratedFileBtoa(@PathParam("id") UUID id, @PathParam("type") String type) {

        String path = generatedFilesFolder;
        String fileName = "";

        if (type.equals("repayment")) {
            SolarPlantRepaymentLog repayment = SolarPlantRepaymentLog.findById(id);
            path += "repayment/";
            fileName = repayment.documentName;
        } else if (type.equals("repayment_reminder")) {
            SolarPlantRepaymentLogReminder repaymentReminder = SolarPlantRepaymentLogReminder.findById(id);
            path += "repayment/";
            fileName = repaymentReminder.documentName;
        }

        try {
            File fileDownload = new File(path + fileName);
            Response.ResponseBuilder response = Response.ok(Base64.getEncoder().encodeToString(FileUtils.readFileToByteArray(fileDownload)), "application/pdf");

            response.header("Content-Disposition", "attachment;filename=" + fileName);
            return response.build();
        }
        catch (Exception e) {
            System.out.println(e.toString());
            return Response.status(404).build();
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("file-delete/{fileId}")
    @Transactional
    public ResultHelper deleteFile(@PathParam("fileId") UUID fileId) {

        FileModel file = FileModel.findById(fileId);

        //rewrite code in optimizing phase // we have no time at the moment for hql
        FileContainerModel fc = FileContainerModel.findById(file.idFileContainer);
        if (securityIdentity.getRoles().contains("user")) {
            if (fc.contextType == 1) {
                if (fc.contextId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }else if ((fc.contextType == 2) ||  (fc.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            } else {
                return new ResultHelper(new ResultCommonObject(404, "Context type does not exists"));
            }
        }

        try {
            String path = uploadsFolder;
            File fileDelete = new File(path + file.fileName);

            fileDelete.delete();
            file.delete();

            FileContainerModel fcm = FileContainerModel.findById(file.idFileContainer);
            if (fcm.rs == 4) {
                fcm.rs = 3;
            } else {
                //if document is not yet verified and file count is 0 then switch to new
                long countFiles = FileModel.count("idFileContainer", file.idFileContainer);
                if (countFiles < 1) {
                    fcm.rs = 1;
                }
            }

            fcm.persist();

            /*
            ActivityModel activity = new ActivityModel();
            activity.title = "File Deleted";
            activity.content = "File has been Deleted";
            activity.showOnUserDashboard = true;
            activity.contentType = "file-plant";
            activity.notificationType = "info";
            activity.contentId = file.idFileContainer;
            activity.userId = fcm.contextId;

            activity.persist();
            */

            ActivityModel activity = new ActivityModel();
            activity.title = "File Deleted";
            activity.content = "File has been Deleted";
            if (fc.contextType == 1) {
                UserBasicInfoModel u = UserBasicInfoModel.find("userId", fc.contextId).firstResult();
                activity.userId = u.id;
            }
            else if ((fc.contextType == 2) || (fc.contextType == 3)) {
                ProjectUserModel pu = ProjectUserModel.find("plantId", fc.contextId).firstResult();
                activity.parentContentId = fc.contextId;
                activity.userId = pu.userId;
            }
            else if (fc.contextType == 4) {
                InvestmentModel im = InvestmentModel.findById(fc.contextId);
                activity.parentContentId = fc.contextId;
                activity.userId = im.userId;
            }

            activity.contentId = file.idFileContainer;
            activity.filename = file.fileName;
            activityService.processActivity(activity, "file_delete");

            return new ResultHelper(new ResultCommonObject(fcm.rs, file.idFileContainer.toString()));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "file not found"));
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("user-dashboard-activity/{userId}")
    @Transactional
    public ResultHelper getUserDashboardActivity(@PathParam("userId") UUID userId) {
        //UserBasicInfoModel user = UserBasicInfoModel.findById(userId);
        List<ActivityModel> activity = ActivityModel.find("userId = ?1 and showOnUserDashboard = ?2", Sort.by("t0").descending(), userId, true).list();

        return new UserResult(200, activity);
    }

    //OK - 3
    //move to activity
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("app-activity/{page}")
    @Transactional
    public List<ActivityModel> getAppActivity(@PathParam("page") Integer page) {
        /*
        List<ActivityModel> activity = ActivityModel.listAll(Sort.by("t0").descending());
        return new UserResult(200, activity);
         */

        //PanacheQuery<ActivityModel> aq = ActivityModel.find("notificationType = ?1", Sort.by("t0").descending(),"info");
        //PanacheQuery<ActivityModel> aq = ActivityModel.find("notificationType != ?1", Sort.by("rs").ascending().and("t0").descending(),"");
        PanacheQuery<ActivityModel> aq = ActivityModel.find("notificationType != ?1 and rs is not null", Sort.by("t0").descending(),"");
        aq.page(Page.of(page, 10));

        List<ActivityModel> al = aq.list();

        return al;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("app-activity-read-all")
    @Transactional
    public ResultCommonObject readAllActivity() {

        ActivityModel.update("rs = 1 where rs < 1");
        return new ResultCommonObject(200, "OK");
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("app-activity/new")
    @Transactional
    public List<ActivityModel> getNewAppActivity() {
        List<ActivityModel> activity = ActivityModel.find("rs < ?1", Sort.by("t0").descending(), 1).list();

       return activity;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("app-activity/latest")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<ActivityModel>  getLatestActivity() {
        PanacheQuery<ActivityModel> aq = ActivityModel.find("rs < ?1", Sort.by("t0").descending(),1);

        return aq.range(0,2).list();
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("count-notifications")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public long  countNotifications() {
        long activityNew = ActivityModel.count("rs < ?1", 1);
        long webInfoNew = WebInfoModel.count("rs", 1);

        long sum = activityNew + webInfoNew;

        return sum;
    }

    //OK - 3
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Path("user-sepa-permission/{userId}")
    @Transactional
    public ResultHelper userSepaPermission(@PathParam("userId") UUID userId,UserSepaPermissionModel sepaRq) {
        try{
           userSepaPermissionService.validateSepa(sepaRq);
            UserBasicInfoModel u = UserBasicInfoModel.find("userId", userId).firstResult();

            if(securityIdentity.getRoles().contains("user")) {
                if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }
            UserSepaPermissionModel sepa = UserSepaPermissionModel.find("userId", userId).firstResult();

            sepa.account = sepaRq.account;
            sepa.fullName = sepaRq.fullName;
            sepa.bic = sepaRq.bic;
            sepa.persist();

            userService.checkSepa(sepa.id);
            return new ResultHelper(200, new ResultCommonObject(200, "Sepa Updated"));
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("user-sepa-permission/{userId}")
    @Transactional
    public ResultHelper getUserSepaPermission(@PathParam("userId") UUID userId) {
        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        UserSepaPermissionModel sepa = UserSepaPermissionModel.find("userId", user.userId).firstResult();
        return new UserResult(200, sepa);
    }

    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("get-file-containers/{userId}")
    @Transactional
    public ResultHelper getFileContainers(@PathParam("userId") UUID userId) {
        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        List<FileContainerModel> fileContainerList = FileContainerModel.find("contextId=?1 and contextType=1 and type=11", Sort.by("sortOrder").ascending(), user.userId).list();
        return new FileContainerResult(200, fileContainerList);
    }

    //OK - 3
    /* DEPRECATED
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("user-power-bill/{userId}")
    @Transactional
    public ResultHelper getUserPowerBill(@PathParam("userId") UUID userId) {
        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        UserPowerBill powerBill = UserPowerBill.find("userId", user.userId).firstResult();
        return new UserResult(200, powerBill);
    }
     */

    //OK - 3
    /* deprecated
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Path("user-power-bill/{userId}")
    @Transactional
    public ResultHelper userPowerBill(@PathParam("userId") UUID userId, UserPowerBill billRq) {

        try{
            userPowerBillService.validateUserPowerBill(billRq);

            UserPowerBill bill = UserPowerBill.find("userId", userId).firstResult();

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (bill.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            bill.billNo = billRq.billNo;
            bill.billPeriod = billRq.billPeriod;
            bill.consumption = billRq.consumption;
            bill.consumptionValue = billRq.consumptionValue;
            bill.provider = billRq.provider;
            bill.contract = billRq.contract;
            bill.netProvider = billRq.netProvider;

            bill.persist();

            userService.checkPowerBill(bill.id);

            return new ResultHelper(200, new ResultCommonObject(200, "Power-Bill Updated"));
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }
    */

    //OK - 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("get-user-status/{userId}")
    @Transactional
    public ResultCommonObject getUserStatus(@PathParam("userId") UUID userId) {

        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultCommonObject(403, "Forbidden");
            }
        }

        UserDataStatus status = UserDataStatus.find("userId", user.userId).firstResult();

        //skip sepa for now
        //if ( (status.basicInfo == true) && (status.sepaInfo == true) && (status.addressInfo == true)) {
        if ( (status.basicInfo == true) && (status.addressInfo == true)) {
            return new ResultCommonObject(200, "OK");
        } else {
            return new ResultCommonObject(206, "FAIL");
        }
    }

    /*
    * INVESTMENT
    * */
    //OK - 2
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Path("investment-add/{userId}")
    @Transactional
    public ResultHelper investmentAdd(@PathParam("userId") UUID userId, InvestmentModel investment) {
        try{

            investment.duration = 12.0;
            investment.interestRate = 2.0;
            investment.repaymentInterval = 1;

            investmentService.validateInvestment(investment);
            investment.rs = 1;

            if(securityIdentity.getRoles().contains("user")) {
                //!!todo get id form user with keycloak userid
                //userId = UUID.fromString(token.getSubject());
            }

            investment.userId = userId;
            investment.persist();

            investmentService.insertInvestmentRepayment(investment);

            //document 1
            FileContainerModel fc = new FileContainerModel();
            fc.contextType = 4;
            fc.type = 41;
            fc.contextId = investment.id;
            fc.rs = 1;
            fc.uploadOnly = false;
            fc.downloadOnly = false;
            fc.backendUploadOnly = false;
            fc.backendOnly = false;
            fc.noStatusUpdate = false;
            fc.sortOrder = 1;
            fc.persist();

            //document 2
            fc = new FileContainerModel();
            fc.contextType = 4;
            fc.type = 42;
            fc.contextId = investment.id;
            fc.rs = 1;
            fc.uploadOnly = false;
            fc.downloadOnly = false;
            fc.backendUploadOnly = false;
            fc.backendOnly = false;
            fc.noStatusUpdate = false;
            fc.sortOrder = 2;
            fc.persist();

            //Backend only files
            fc = new FileContainerModel();
            fc.contextType = 4;
            fc.type = 43;
            fc.contextId = investment.id;
            fc.rs = 1;
            fc.uploadOnly = true;
            fc.downloadOnly = false;
            fc.backendUploadOnly = false;
            fc.backendOnly = true;
            fc.noStatusUpdate = true;
            fc.sortOrder = 3;
            fc.persist();

            return new InvestmentResult(200, investment);
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    //OK - 2
    @POST
    @RolesAllowed({"manager", "admin"})
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Path("investment-update/{investmentId}")
    @Transactional
    public ResultHelper investmentUpdateBkp(@PathParam("investmentId") UUID investmentId, InvestmentModel investment) {
        try{
            investmentService.validateInvestment(investment);

            InvestmentModel i = InvestmentModel.findById(investmentId);

            //todo solar.family must define status until we are able to change investment data!!!
            i.amount = investment.amount;
            i.duration = investment.duration;
            i.interestRate = investment.interestRate;
            i.repaymentInterval = investment.repaymentInterval;
            i.repaymentStart = investment.repaymentStart;

            i.persist();

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (i.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            return new ResultHelper(200, new ResultCommonObject(200, "investment updated"));
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Path("investment-update")
    @Transactional
    public ResultHelper investmentUpdate(InvestmentModel investment) {
        try{
            investmentService.validateInvestment(investment);
            InvestmentModel i = InvestmentModel.findById(investment.id);

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (i.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            //todo solar.family must define status until we are able to change investment data!!!
            i.amount = investment.amount;
            i.duration = investment.duration;
            i.interestRate = investment.interestRate;
            i.repaymentInterval = investment.repaymentInterval;
            i.repaymentStart = investment.repaymentStart;

            i.persist();
            investmentService.insertInvestmentRepayment(i);

            return new ResultHelper(200, new ResultCommonObject(200, "investment updated"));
        } catch (ConstraintViolationException e) {
            ObjectNode error = parserHelper.parseConstraintViolationException(e);
            return new ResultHelper(400, error);
        }
    }

    //OK - 2
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("investment-get/{investmentId}")
    @Transactional
    public InvestmentResult getInvestment(@PathParam("investmentId") UUID investmentId) {

        InvestmentModel i = InvestmentModel.findById(investmentId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (i.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
               new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        return new InvestmentResult(200, i);
    }

    //OK - 2
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("investment-list")
    @Transactional
    public InvestmentResult getInvestmentList() {

        List<InvestmentModel> i = InvestmentModel.listAll();
        return new InvestmentResult(200,i);
    }

    //OK - 2
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("investment-list/{userId}")
    @Transactional
    public InvestmentResult getInvestmentList(@PathParam("userId") UUID userId) {

        //this should be optimized in next phase
        //fix to user -- not keycloak user
        if(securityIdentity.getRoles().contains("user")) {
            if (userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        List<InvestmentModel> i = InvestmentModel.find("userId=?1 and rs != 99", Sort.by("t0").descending(), userId).list();
        return new InvestmentResult(200,i);
    }

    //OK 2
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("investment-file-containers/{id}")
    @Transactional
    public ResultHelper getInvestmentFileContainers(@PathParam("id") UUID id) {

        InvestmentModel investment = InvestmentModel.findById(id);
        UserBasicInfoModel user = UserBasicInfoModel.findById(investment.userId);
        Boolean showBackendContainers = true;
        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            showBackendContainers = false;
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        if (showBackendContainers == true) {
            List<FileContainerModel> fileContainerList = FileContainerModel.find("contextId=?1 and contextType=4", Sort.by("type").ascending(), id).list();
            return new FileContainerResult(200, fileContainerList);
        } else {
            List<FileContainerModel> fileContainerList = FileContainerModel.find("contextId=?1 and contextType=4 and backendOnly=?2", Sort.by("type").ascending(), id,false).list();
            return new FileContainerResult(200, fileContainerList);
        }
    }

    //OK 2
    @GET
    @RolesAllowed({"user","manager", "admin"})
    @Path("get-investment-user/{investmentId}")
    @Transactional
    public ResultHelper getInvestmentUser(@PathParam("investmentId") UUID investmentId) {
        InvestmentModel investment = InvestmentModel.findById(investmentId);

        if (investment != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(investment.userId);

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            ObjectMapper mapper = new ObjectMapper();
            ObjectNode node = mapper.createObjectNode();

            node.put("email", user.email);
            node.put("firstName", user.firstName);
            node.put("lastName", user.lastName);
            node.put("id", user.id.toString());

            return new ResultHelper(200, node);
        } else {
            return new ResultHelper(new ResultCommonObject(401, "not found"));
        }
    }

    //OK - 2
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("investment-update-file-status/{id}/{status}")
    @Transactional
    public ResultHelper updateInvestmentFileStatus(@PathParam("id") UUID id, @PathParam("status") Boolean status) {
        try {
            InvestmentModel investment = InvestmentModel.findById(id);
            investment.investmentFilesVerifiedByBackendUser = status;
            investment.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-investment-status/{id}/{type}/{status}")
    @Transactional
    public ResultHelper updateInvestmentStatus(@PathParam("id") UUID id, @PathParam("type") String type, @PathParam("status") Boolean status) {
        try {
            InvestmentModel investment = InvestmentModel.findById(id);

            System.out.println(type);
            System.out.println(status);

            if (type.equals("contractPaid")) {
                System.out.println(type);
                investment.contractPaid = status;
            } else if ( type.equals("contractFinalized")) {
                System.out.println(type);
                investment.contractFinalized = status;

                investment.repaymentStart = LocalDate.now().plusYears(Long.valueOf(1));
            }
            investment.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }


    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("webinfo-plant-size/{id}")
    @Transactional
    public String updateInvestmentStatus(@PathParam("id") UUID id) {
        UserBasicInfoModel user = UserBasicInfoModel.findById(id);

        //System.out.println(user.email);

        WebInfoModel webInfo = WebInfoModel.find("email = ?1 and rs = ?2",  Sort.by("t0").descending(), user.email.toLowerCase(), 2).firstResult();
        //System.out.println(webInfo.webCalculation);
        try {
            return webInfo.webCalculation;
        } catch (Exception e) {
            return "";
        }


        //return new ResultHelper(new ResultCommonObject(200, webInfo.webCalculation));
    }

    class UserResult extends ResultHelper {
        public long records = -1 ;

        public UserResult(Integer status, UserBasicInfoModel user) {
            super();
            this.status = status;
            this.payload = user;
        }

        /*
        public UserResult(Integer status, Long records, List<UserBasicInfoModel> users) {
            super();
            this.records = records;
            this.status = status;
            this.payload = users;
        }

         */

        public UserResult(Integer status, Long records, List<UserListModel> users) {
            super();
            this.records = records;
            this.status = status;
            this.payload = users;
        }

        public UserResult(Integer status, UserAddressModel address) {
            super();
            this.status = status;
            this.payload = address;
        }

        public UserResult(Integer status, UserSepaPermissionModel sepa) {
            super();
            this.status = status;
            this.payload = sepa;
        }

        public UserResult(Integer status, SolarPlantPowerBill bill) {
            super();
            this.status = status;
            this.payload = bill;
        }

        public UserResult(Integer status, List<ActivityModel> activity) {
            super();
            this.status = status;
            this.payload = activity;
        }

        public Long getRecords() {
            return records;
        }
    }

    //move this to separate file in optimizing phase // no time
    class FileResult extends ResultHelper {

        public FileResult(Integer status, List<FileModel> files) {
            super();
            this.status = status;
            this.payload = files;
        }
    }

    //move this to separate file in optimizing phase // no time
    class InvestmentResult extends ResultHelper {

        public InvestmentResult(Integer status, List<InvestmentModel> investment) {
            super();
            this.status = status;
            this.payload = investment;
        }

        public InvestmentResult(Integer status, InvestmentModel investment) {
            super();
            this.status = status;
            this.payload = investment;
        }
    }

    //move this to separate file in optimizing phase // no time
    class FileContainerResult extends ResultHelper {
        public FileContainerResult(Integer status, List<FileContainerModel> files) {
            super();
            this.status = status;
            this.payload = files;
        }
    }
}