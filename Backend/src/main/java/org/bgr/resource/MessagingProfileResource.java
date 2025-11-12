package org.bgr.resource;

import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.helper.MessagingBackendUser;
import org.bgr.helper.MessagingPayloadBuilder;
import org.bgr.helper.MessagingResponseBuilder;
import org.bgr.model.ProfileModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.eclipse.microprofile.openapi.annotations.parameters.RequestBody;

import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.List;
import java.util.UUID;

@Path("/profile")
public class MessagingProfileResource {
    @Inject
    SecurityIdentity securityIdentity;

    @GET
    @Path("/get-profiles")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response getProfiles() {
        return MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<List<ProfileModel>>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                ProfileModel.listAll(), null));
    }

    @GET
    @Path("/get-profile-old/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response getProfile(@PathParam("userId") UUID userId) {
        return MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                ProfileModel.find("id = ?1", userId).firstResult(), null));
    }

    //todo change response
    @GET
    @Path("/get-auth-profile")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public MessagingBackendUser getUserProfileNew(@PathParam("id") UUID id) {

        MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();

        return backendUser;

        /*
        if(!securityIdentity.getRoles().contains("user")) {
            System.out.println("This is backend user!");

            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();
            System.out.println(backendUser.id);
            System.out.println(backendUser.fullName);
        }
        */
    }

    @GET
    @Path("/get-profile/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public UserBasicInfoModel getProfileNew(@PathParam("id") UUID id) {
        UserBasicInfoModel user = UserBasicInfoModel.find("id = ?1", id).firstResult();
        return user;
    }

    @POST
    @Path("/create-profile")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response createProfile(ProfileModel profileModel) {
        try{
            profileModel.persist();
            return getProfile(profileModel.id);
        }
        catch (Exception e){
            return MessagingResponseBuilder.Build(500,
                    new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.InternalServerError, null, null));
        }
    }

    @PUT
    @Path("/edit-profile/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response editProfile(@PathParam("userId") UUID userId, @RequestBody ProfileModel profileModel) {
        ProfileModel profile = ProfileModel.find("id = ?1", userId).firstResult();
        if(profile == null)  return MessagingResponseBuilder.Build(404,
                new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
        profile.fullName = profileModel.fullName;
        profile.avatar = profileModel.avatar;
        profile.persist();
        return getProfile(profile.id);
    }

    @DELETE
    @Path("/delete-profile-by-id/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response deleteProfileById(@PathParam("userId") UUID userId) {
        long result = ProfileModel.delete("id = ?1", userId);
        return result > 0 ? MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok, null, null)) :
                MessagingResponseBuilder.Build(404, new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
    }
}
