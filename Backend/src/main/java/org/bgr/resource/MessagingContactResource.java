package org.bgr.resource;

import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.helper.MessagingBackendUser;
import org.bgr.helper.MessagingPayloadBuilder;
import org.bgr.helper.MessagingResponseBuilder;
import org.bgr.model.ContactModel;
import io.vertx.core.logging.Logger;
import io.vertx.core.logging.LoggerFactory;
import org.eclipse.microprofile.jwt.JsonWebToken;
import org.eclipse.microprofile.openapi.annotations.parameters.RequestBody;

import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.List;
import java.util.UUID;

@Path("/contact")
public class MessagingContactResource {

    @Inject
    JsonWebToken token;

    @Inject
    SecurityIdentity securityIdentity;

    public static final Logger logger = LoggerFactory.getLogger(MessagingContactResource.class);

    @GET
    @Path("/get-contacts/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response getContacts(@PathParam("userId") UUID userId) {
        return MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<List<ContactModel>>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                ContactModel.find("belongsTo = ?1", userId).list(), null));
    }

    @GET
    @Path("/get-contact/{contactId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response getContact(@PathParam("contactId") UUID contactId) {
        return MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<ContactModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                ContactModel.find("id = ?1", contactId).firstResult(), null));
    }

    //OLD
    @POST
    @Path("/create-contact-old")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response createContactOld(ContactModel contactModel) {
        try{
            contactModel.persist();
            return getContact(contactModel.id);
        }
        catch (Exception e){
            logger.error(e.getMessage());
            return MessagingResponseBuilder.Build(500,
                    new MessagingPayloadBuilder<ContactModel>().buildPayload(MessagingResponseBuilder.StatusCode.InternalServerError, null, null));
        }
    }

    @GET
    @Path("/create-contact/{contactId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public void createContact(@PathParam("contactId") UUID contactId) {
        System.out.println(token.getSubject());
        System.out.println(securityIdentity.getRoles());

        UUID userId = UUID.fromString(token.getSubject());
        if(!securityIdentity.getRoles().contains("user")) {
            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();

            userId = backendUser.id;
        }

        try{
            long hasPair = ContactModel.count("belongsTo=?1 and contactId=?2", userId, contactId);

            if (hasPair == 0) {
                ContactModel contact = new ContactModel();
                contact.belongsTo = userId;
                contact.contactId = contactId;
                contact.persist();

                contact = new ContactModel();
                contact.belongsTo = contactId;
                contact.contactId = userId;
                contact.persist();
            }
            System.out.println(hasPair);

        }catch (Exception e) {
            System.out.println(e);
            logger.error(e.getMessage());
        }
        /*
        try{
            contactModel.persist();
            return getContact(contactModel.id);
        }
        catch (Exception e){
            logger.error(e.getMessage());
            return MessagingResponseBuilder.Build(500,
                    new MessagingPayloadBuilder<ContactModel>().buildPayload(MessagingResponseBuilder.StatusCode.InternalServerError, null, null));
        }
        */
    }

    @PUT
    @Path("/edit-contact/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response editContact(@PathParam("userId") UUID userId, @RequestBody ContactModel contactModel) {
        ContactModel contact = ContactModel.find("id = ?1", userId).firstResult();
        if(contact == null)  return MessagingResponseBuilder.Build(404,
                new MessagingPayloadBuilder<ContactModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
        contact.belongsTo = contactModel.belongsTo;
        contact.contactId = contactModel.contactId;
        contact.persist();
        return getContact(contact.id);
    }

    @DELETE
    @Path("/delete-contact-by-id/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response deleteContactById(@PathParam("userId") UUID userId) {
        long result = ContactModel.delete("id = ?1", userId);
        return result > 0 ? MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<ContactModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok, null, null)) :
                MessagingResponseBuilder.Build(404, new MessagingPayloadBuilder<ContactModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
    }
}
