package org.bgr.resource;

import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.panache.common.Page;
import io.quarkus.panache.common.Sort;
import io.quarkus.security.identity.SecurityIdentity;
import io.vertx.core.logging.Logger;
import io.vertx.core.logging.LoggerFactory;
import org.bgr.helper.MessagingBackendUser;
import org.bgr.helper.MessagingPayloadBuilder;
import org.bgr.helper.MessagingResponseBuilder;
import org.bgr.helper.NativeQueryToJson;
import org.bgr.model.AttachmentModel;
import org.bgr.model.MessageModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.bgr.service.MessageService;
import org.eclipse.microprofile.jwt.JsonWebToken;
import org.eclipse.microprofile.openapi.annotations.parameters.RequestBody;
import org.jboss.resteasy.annotations.jaxrs.PathParam;
import org.jboss.resteasy.annotations.providers.multipart.MultipartForm;
import org.jboss.resteasy.plugins.providers.multipart.InputPart;
import org.jboss.resteasy.plugins.providers.multipart.MultipartFormDataInput;

import javax.inject.Inject;
import javax.persistence.EntityManager;
import javax.persistence.Tuple;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.UriInfo;
import java.io.File;
import java.time.LocalDateTime;
import java.util.*;

import static javax.ws.rs.core.Response.ok;
import static org.apache.commons.io.FilenameUtils.getName;

@Path("/message")
public class MessagingMessageResource {
    public static final String FILE_FORM_FIELD = "file";
    public static final String MESSAGE_FORM_FIELD = "message";
    public static final String SENDER_ID_FORM_FIELD = "senderId";
    public static final String RECIPIENT_ID_FORM_FIELD = "recipientId";
    public static final Logger logger = LoggerFactory.getLogger(MessagingMessageResource.class);

    @Inject
    JsonWebToken token;

    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    private MessageService messageService;

/*
    @GET
    @Path("/get-messages/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response getMessages(@PathParam("userId") UUID userId, @Context UriInfo uriInfo) {
        List<MessageModel> userMessages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").descending(), userId).list();
        if(userMessages == null)  return MessagingResponseBuilder.Build(404, new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));

        System.out.println("--------------------START---------------------------");

        System.out.println(uriInfo);
        System.out.println(userMessages.size());

        userMessages.forEach(message -> {
            messageService.addAttachmentLinks(uriInfo, message);
            message.getReactions().forEach(reaction -> {
                message.addReaction(reaction);
            });

            System.out.println(message);
            System.out.println(message.messageReactions);
            System.out.println(message.getAttachments());

        });

        userMessages.forEach(message -> {
            System.out.println(message);
            System.out.println(message.messageReactions);
            System.out.println(message.getAttachments());
        } );

        Response response = MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<List<Message>>().buildPayload(MessagingResponseBuilder.StatusCode.Ok, MessagingMessageHelper.GetMessages(userMessages, userId), null));

        userMessages.forEach(message -> {
            if(message.recipientId == userId){
                message.read = true;
                message.persist();
            }
        } );

        System.out.println("--------------------END---------------------------");

        return response;
    }
*/

/*
    @GET
    @Path("/get-messages/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<MessageModel> getMessages(@PathParam("userId") UUID userId, @Context UriInfo uriInfo) {
        List<MessageModel> userMessages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").ascending(), userId).list();


        if(userMessages == null)  return MessagingResponseBuilder.Build(404,
                new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
        userMessages.forEach(message -> {
            messageService.addAttachmentLinks(uriInfo, message);
            message.getReactions().forEach(reaction -> {
                message.addReaction(reaction);
            });
        });
        Response response = MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<List<Message>>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                MessagingMessageHelper.GetMessages(userMessages, userId), null));

        userMessages.forEach(message -> {
            if(message.recipientId == userId){
                message.read = true;
                message.persist();
            }
        } );


        return userMessages;
    }
    */

    /*
    @GET
    @Path("/get-messages/{userId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<MessageModel> getMessages(@PathParam("userId") UUID userId, @Context UriInfo uriInfo) {
        List<MessageModel> userMessages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").ascending(), userId).list();

        userMessages.forEach(message -> {
            messageService.addAttachmentLinks(uriInfo, message);
            message.getReactions().forEach(reaction -> {
                message.addReaction(reaction);
            });
        });

        userMessages.forEach(message -> {
            if(message.recipientId == userId){
                message.read = true;
                message.persist();
            }
        } );


        return userMessages;
    }
    */

    @GET
    @Path("/get-messages/{userId}/{currentPage}/{records}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<MessageModel> getMessages(@PathParam("userId") UUID userId, @PathParam("currentPage") Integer currentPage, @PathParam("records") Integer records, @Context UriInfo uriInfo) {
        //Integer perPage = 10;
        //records = 10;
        System.out.println("get-messages");
        UUID recipientId = UUID.fromString(token.getSubject());
        if(!securityIdentity.getRoles().contains("user")) {
            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();
            recipientId = backendUser.id;
        }

        PanacheQuery<MessageModel> messages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").descending(), userId);
        List<MessageModel> userMessages = messages.page(Page.of(currentPage, records)).list();

        Collections.reverse(userMessages);

        /*
        userMessages.forEach(message -> {
            System.out.println(message.t0);
            System.out.println(message.message);
        });
         */

        userMessages.forEach(message -> {
            messageService.addAttachmentLinks(uriInfo, message);
            message.getReactions().forEach(reaction -> {
                message.addReaction(reaction);
            });
        });

        /* make it simpler
        UUID finalRecipientId = recipientId;
        userMessages.forEach(message -> {
            System.out.println(message.recipientId);
            System.out.println(finalRecipientId);
            if(message.recipientId == finalRecipientId){
                message.read = true;
                message.persist();
            }
        });
         */

        //mark all messages for user as read
        // !!!do upgrade here when you have working frontend to test
        MessageModel.update("read = true where recipientId = ?1 and read = false", recipientId);

        return userMessages;
    }

    @GET
    @Path("/get-messages-frontend/{contactId}/{currentPage}/{records}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<MessageModel> getMessagesFrontend(@PathParam("contactId") UUID contactId, @PathParam("currentPage") Integer currentPage, @PathParam("records") Integer records, @Context UriInfo uriInfo) {

        UUID recipientId = UUID.fromString(token.getSubject());

        UserBasicInfoModel user = UserBasicInfoModel.find("userId", recipientId).firstResult();
        //UUID userId = user.id;
        //System.out.println(userId);

        PanacheQuery<MessageModel> messages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").descending(), user.id);
        List<MessageModel> userMessages = messages.page(Page.of(currentPage, records)).list();

        Collections.reverse(userMessages);

        //List<MessageModel> userMessages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").ascending(), userId).list();

        userMessages.forEach(message -> {
            messageService.addAttachmentLinks(uriInfo, message);
            message.getReactions().forEach(reaction -> {
                message.addReaction(reaction);
            });
        });

        /*
        System.out.println("update messages to read");
        userMessages.forEach(message -> {
            if(message.recipientId == userId){
                message.read = true;
                message.persist();
            }
        } );
         */

        MessageModel.update("read = true where recipientId = ?1 and read = false", user.id);

        return userMessages;
    }


    @GET
    @Path("/get-latest-chats")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<ObjectNode> getLatestChat() {
        System.out.println("latest chat for users");
        System.out.println(token.getSubject());

        String userId = token.getSubject();
        if(!securityIdentity.getRoles().contains("user")) {
            System.out.println("This is backend user!");

            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();
            System.out.println(backendUser.id);
            System.out.println(backendUser.fullName);

            userId = backendUser.id.toString();
        }

        MessageModel mm = new MessageModel();
        EntityManager em = mm.getEntityManager();

        String old = "select cast(mm.recipientid as varchar) as contactid, concat(ubi.lastname, ' ', ubi.firstname) as fullName, mm.message, mm.t0, mm.read, cast(mm.keycloakid as varchar) as originid"
                +" from msg_message mm "
                +" right join user_basic_info ubi  on ubi.id = mm.recipientid"
                +" where mm.id in ("
                +"	select filteredId.id"
                +"	from ("
                +"		SELECT mm1.id, mm1.t0"
                +"		FROM msg_message mm1"
                +"		LEFT OUTER JOIN msg_message mm2 on mm1.recipientid = mm2.recipientid"
                +"		AND mm1.t0 < mm2.t0"
                +"		WHERE mm2.recipientid IS NULL"
                +"		  AND mm1.senderid ='"+userId+"'"
                +"		  AND mm1.message IS NOT NULL"
                +"		  "
                +"		union all"
                +"		"
                +"		SELECT mm1.id, mm1.t0"
                +"		FROM msg_message mm1"
                +"		LEFT OUTER JOIN msg_message mm2 ON mm1.senderid = mm2.senderid"
                +"		AND mm1.t0 < mm2.t0"
                +"		WHERE mm2.senderid IS NULL"
                +"		  AND mm1.recipientid ='"+userId+"'"
                +"		  AND mm1.message IS NOT NULL"
                +"		  "
                +"		order by t0 desc"
                +"		OFFSET 0 LIMIT 30"
                +"	) as filteredId"
                +" )"
                +" order by t0 desc";

        /* working
        select distinct on (subset.contactid) contactid  as contactid, cast(subset.id as varchar) as id, subset.message, cast(subset.keycloakid as varchar) as originid, subset.t0, concat(ubi.lastname, ' ', ubi.firstname) as fullName, subset.read
        from (
            SELECT
              mm1.id,
              mm1.t0,
              mm1.message,
              mm1.recipientid as contact,
              cast(mm1.recipientid as varchar) as contactid,
              mm1.keycloakid,
              mm1.read
            FROM
              msg_message mm1
              LEFT OUTER JOIN msg_message mm2 on mm1.recipientid = mm2.recipientid AND mm1.t0 < mm2.t0
            WHERE
              mm2.recipientid IS NULL
              AND mm1.senderid = '21d8e305-6138-4afd-a0e5-ae2f23d7b4b6'
              AND mm1.message IS NOT NULL

             union

             SELECT
              mm1.id,
              mm1.t0,
              mm1.message,
              mm1.senderid  as contact,
              cast(mm1.senderid as varchar) as contactid,
              mm1.keycloakid,
              mm1.read
            FROM
              msg_message mm1
              LEFT OUTER JOIN msg_message mm2 ON mm1.senderid = mm2.senderid AND mm1.t0 < mm2.t0
            WHERE
              mm2.senderid IS NULL
              AND mm1.recipientid = '21d8e305-6138-4afd-a0e5-ae2f23d7b4b6'
              AND mm1.message IS NOT NULL

            order by t0 desc
            limit 10
        ) as subset
        left join user_basic_info ubi on ubi.id = subset.contact
        group by subset.contactid, subset.id, subset.message, subset.keycloakid, subset.t0, ubi.lastname, ubi.firstname,subset.read

        //updated
        select t.* from(
        select distinct on (subset.contactid) contactid  as contactid, cast(subset.id as varchar) as id, subset.message, cast(subset.keycloakid as varchar) as originid, subset.t0, concat(ubi.lastname, ' ', ubi.firstname) as fullName, subset.read
        from (
            SELECT
              mm1.id,
              mm1.t0,
              mm1.message,
              mm1.recipientid as contact,
              cast(mm1.recipientid as varchar) as contactid,
              mm1.keycloakid,
              mm1.read
            FROM
              msg_message mm1
              LEFT OUTER JOIN msg_message mm2 on mm1.recipientid = mm2.recipientid AND mm1.t0 < mm2.t0
            WHERE
              mm2.recipientid IS NULL
              AND mm1.senderid = '21d8e305-6138-4afd-a0e5-ae2f23d7b4b6'
              AND mm1.message IS NOT NULL

             union

             SELECT
              mm1.id,
              mm1.t0,
              mm1.message,
              mm1.senderid  as contact,
              cast(mm1.senderid as varchar) as contactid,
              mm1.keycloakid,
              mm1.read
            FROM
              msg_message mm1
              LEFT OUTER JOIN msg_message mm2 ON mm1.senderid = mm2.senderid AND mm1.t0 < mm2.t0
            WHERE
              mm2.senderid IS NULL
              AND mm1.recipientid = '21d8e305-6138-4afd-a0e5-ae2f23d7b4b6'
              AND mm1.message IS NOT NULL

            order by t0 desc
            limit 1000
        ) as subset
        left join user_basic_info ubi on ubi.id = subset.contact
    ) as t
    order by t.t0 desc

        * */

        String query = "select t.* from("
            + " select distinct on (subset.contactid) contactid  as contactid, cast(subset.id as varchar) as id, subset.message, cast(subset.keycloakid as varchar) as originid, subset.t0, concat(ubi.lastname, ' ', ubi.firstname) as fullName, subset.read"
            +" from ( SELECT mm1.id, mm1.t0, mm1.message, mm1.recipientid as contact, cast(mm1.recipientid as varchar) as contactid, mm1.keycloakid, mm1.read"
            +" FROM msg_message mm1"
            +" LEFT OUTER JOIN msg_message mm2 on mm1.recipientid = mm2.recipientid AND mm1.t0 < mm2.t0"
            +" WHERE mm2.recipientid IS NULL"
            +" AND mm1.senderid = '"+userId+"'"
            +" AND mm1.message IS NOT NULL"

            +" union"

            +" SELECT mm1.id, mm1.t0, mm1.message, mm1.senderid  as contact, cast(mm1.senderid as varchar) as contactid, mm1.keycloakid, mm1.read"
            +" FROM msg_message mm1"
            +" LEFT OUTER JOIN msg_message mm2 ON mm1.senderid = mm2.senderid AND mm1.t0 < mm2.t0"
            +" WHERE mm2.senderid IS NULL"
            +" AND mm1.recipientid = '"+userId+"'"
            +" AND mm1.message IS NOT NULL"

            +" order by t0 desc"
            +" limit 30"
            +" ) as subset"
            +" left join user_basic_info ubi on ubi.id = subset.contact"
            //+" group by subset.contactid, subset.id, subset.message, subset.keycloakid, subset.t0, ubi.lastname, ubi.firstname,subset.read";
            +" ) as t"
            +" order by t.t0 desc";

        //System.out.println(query);

        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(query, Tuple.class).getResultList());

        return res;
    }

    @GET
    @Path("/search-chat/{user}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<ObjectNode> searchChat(@PathParam("user") String user) {
        System.out.println("------------------------------search chat------------------------------------");
        System.out.println(user);

        String userId = token.getSubject();
        if(!securityIdentity.getRoles().contains("user")) {
            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();
            userId = backendUser.id.toString();
        }

        //full text search firstname / lastname / email
        String userIds = UserBasicInfoModel.fullTextSearch(user);
        //System.out.println(userIds);

        MessageModel mm = new MessageModel();
        EntityManager em = mm.getEntityManager();

        /*
        String query = "select cast(mm.recipientid as varchar) as contactid, concat(ubi.lastname, ' ', ubi.firstname) as fullName, mm.message, mm.t0, mm.read, cast(mm.keycloakid as varchar) as originid"
                +" from msg_message mm "
                +" right join user_basic_info ubi  on ubi.id = mm.recipientid"
                +" where mm.id in ("
                +"	select filteredId.id"
                +"	from ("
                +"		SELECT mm1.id, mm1.t0"
                +"		FROM msg_message mm1"
                +"		left outer join msg_message mm2 on mm1.recipientid = mm2.recipientid"
                +"		AND mm1.t0 < mm2.t0"
                +"		WHERE mm2.recipientid IS NULL"
                +"		  AND mm1.senderid ='"+userId+"'"
                +"		  AND mm1.message IS NOT NULL"
                +"		  "
                +"		union all"
                +"		"
                +"		SELECT mm1.id, mm1.t0"
                +"		FROM msg_message mm1"
                +"		LEFT OUTER JOIN msg_message mm2 ON mm1.senderid = mm2.senderid"
                +"		AND mm1.t0 < mm2.t0"
                +"		WHERE mm2.senderid IS NULL"
                +"		  AND mm1.recipientid ='"+userId+"'"
                +"		  AND mm1.message IS NOT NULL"
                +"		  "
                +"		order by t0 desc"
                +"		OFFSET 0 LIMIT 10"
                +"	) as filteredId"
                +" )"
                +" AND mm.recipientid IN ("+userIds+")"
                +" order by t0 desc";
         */

        String query = "select t.* from("
                + " select distinct on (subset.contactid) contactid  as contactid, cast(subset.id as varchar) as id, subset.message, cast(subset.keycloakid as varchar) as originid, subset.t0, concat(ubi.lastname, ' ', ubi.firstname) as fullName, subset.read"
                +" from ( SELECT mm1.id, mm1.t0, mm1.message, mm1.recipientid as contact, cast(mm1.recipientid as varchar) as contactid, mm1.keycloakid, mm1.read"
                +" FROM msg_message mm1"
                +" LEFT OUTER JOIN msg_message mm2 on mm1.recipientid = mm2.recipientid AND mm1.t0 < mm2.t0"
                +" WHERE mm2.recipientid IS NULL"
                +" AND mm1.senderid = '"+userId+"'"
                +" AND mm1.message IS NOT NULL"

                +" union"

                +" SELECT mm1.id, mm1.t0, mm1.message, mm1.senderid  as contact, cast(mm1.senderid as varchar) as contactid, mm1.keycloakid, mm1.read"
                +" FROM msg_message mm1"
                +" LEFT OUTER JOIN msg_message mm2 ON mm1.senderid = mm2.senderid AND mm1.t0 < mm2.t0"
                +" WHERE mm2.senderid IS NULL"
                +" AND mm1.recipientid = '"+userId+"'"
                +" AND mm1.message IS NOT NULL"

                +" order by t0 desc"
                +" limit 30"
                +" ) as subset"
                +" left join user_basic_info ubi on ubi.id = subset.contact"
                +" where subset.contact IN ("+userIds+")"
                //+" group by subset.contactid, subset.id, subset.message, subset.keycloakid, subset.t0, ubi.lastname, ubi.firstname,subset.read";
                +" ) as t"
                +" order by t.t0 desc";



        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(query, Tuple.class).getResultList());

        return res;
    }

    /*
    public Response getLatestChat(@PathParam("userId") UUID userId, @Context UriInfo uriInfo) {
        List<MessageModel> userMessages = MessageModel.find("senderId = ?1 OR recipientId = ?1", Sort.by("t0").ascending(), userId).list();
        if(userMessages == null)  return MessagingResponseBuilder.Build(404,
                new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
        userMessages.forEach(message -> {
            messageService.addAttachmentLinks(uriInfo, message);
            message.getReactions().forEach(reaction -> {
                message.addReaction(reaction);
            });
        });
        Response response = MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<List<Message>>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                MessagingMessageHelper.GetMessages(userMessages, userId), null));
        userMessages.forEach(message -> {
            if(message.recipientId == userId){
                message.read = true;
                message.persist();
            }
        } );
        return response;
    }
    */

    @GET
    @Path("/get-message-by-id/{messageId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response getMessageById(@PathParam("messageId") UUID messageId, @Context UriInfo uriInfo) {
        MessageModel messageModel = MessageModel.find("id = ?1", messageId).firstResult();
        if(messageModel == null)  return MessagingResponseBuilder.Build(404,
                new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
        messageService.addAttachmentLinks(uriInfo, messageModel);
        messageModel.getReactions().forEach(reaction -> {
            messageModel.addReaction(reaction);
        });
        return MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok,
                messageModel, null));
    }

    @POST
    @Path("/create-message")
    @Consumes(MediaType.MULTIPART_FORM_DATA)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response createMessage(@MultipartForm MultipartFormDataInput input, @Context UriInfo uriInfo) {
        try{
            logger.debug("createMessage");

            // TODO check right
            System.out.println(token.getSubject());

            if(!securityIdentity.getRoles().contains("user")) {
                System.out.println("cant do shit");
            }

            Map<String, List<InputPart>> form = input.getFormDataMap();
            List<InputPart> messageList = form.get(MESSAGE_FORM_FIELD);
            List<InputPart> senderList = form.get(SENDER_ID_FORM_FIELD);
            List<InputPart> recipientList = form.get(RECIPIENT_ID_FORM_FIELD);
            List<InputPart> files = form.get(FILE_FORM_FIELD);
            if (messageList == null || messageList.isEmpty()) throw new IllegalArgumentException("Message not found!");

            MessageModel message = new MessageModel(messageList.get(0).getBody(String.class, null), new HashSet<>(), UUID.fromString(senderList.get(0).getBody(String.class, null)),
                    UUID.fromString(recipientList.get(0).getBody(String.class, null)), UUID.fromString(token.getSubject()));

            Set<String> strings = messageService.saveAttachments(files);
            message.t0 = LocalDateTime.now();
            strings.forEach(s -> message.getAttachments().add(new AttachmentModel(s, message)));
            message.persist();

            messageService.addAttachmentLinks(uriInfo, message);

            //message.read = true;

            return MessagingResponseBuilder.Build(200,
                    new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok, message, null));
        }
        catch (Exception e){
            logger.debug(e.getMessage());
            return MessagingResponseBuilder.Build(500,
                    new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.InternalServerError, null, null));
        }
    }

    @PUT
    @Path("/edit-message/{messageId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response editMessage(@PathParam("messageId") UUID messageId, @RequestBody MessageModel messageModel, @Context UriInfo uriInfo) {

        MessageModel message = MessageModel.find("id = ?1", messageId).firstResult();


        UUID userId;
        if(!securityIdentity.getRoles().contains("user")) {
            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();
            userId = backendUser.id;
        } else {
            UserBasicInfoModel user = UserBasicInfoModel.find("userId", UUID.fromString(token.getSubject())).firstResult();
            userId = user.id;
        }

        System.out.println(message.senderId);
        System.out.println(userId);

        if (message.senderId.equals(userId) == true) {
            if(message == null)  return MessagingResponseBuilder.Build(404,
                    new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
            message.message = messageModel.message;
            message.edited = true;
            message.persist();
            return getMessageById(messageModel.id, uriInfo);
        } else {
            return MessagingResponseBuilder.Build(403,
                    new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.Forbidden, null, null));
        }
    }

    @DELETE
    @Path("/delete-message-by-id/{messageId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response deleteMessageById(@PathParam("messageId") UUID messageId, @Context UriInfo uriInfo) {
        MessageModel message = MessageModel.find("id = ?1", messageId).firstResult();

        UUID userId;
        if(!securityIdentity.getRoles().contains("user")) {
            MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();
            userId = backendUser.id;
        } else {
            UserBasicInfoModel user = UserBasicInfoModel.find("userId", UUID.fromString(token.getSubject())).firstResult();
            userId = user.id;
        }

        if(message == null)  return MessagingResponseBuilder.Build(404,
                new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));

        if (message.senderId.equals(userId) == true) {
            message.deleted = true;
            message.persist();
            return getMessageById(messageId, uriInfo);
        } else {
            return MessagingResponseBuilder.Build(403,
                    new MessagingPayloadBuilder<MessageModel>().buildPayload(MessagingResponseBuilder.StatusCode.Forbidden, null, null));
        }
    }

    @GET
    @Path("/attachment/{attachmentId}")
    @Produces(MediaType.APPLICATION_OCTET_STREAM)
    public Response downloadAttachment(@PathParam("attachmentId") UUID attachmentId) {
        AttachmentModel attachment = messageService.getAttachment(attachmentId);
        String fileName = getName(attachment.getFileLocation());
        Response.ResponseBuilder response = ok(new File(attachment.getFileLocation()));
        response.header("Content-Disposition", "attachment; filename=" + fileName);
        return response.build();
    }

    @GET
    @Path("/unread-backend")
    public List<ObjectNode> unreadMessagesBackend() {
        MessagingBackendUser backendUser = MessagingBackendUser.getMessagingBackendUser();


        //Long unreadNr = MessageModel.count("read=?1 and recipientId=?2 and deleted=?3", false, backendUser.id, false);

        /*
        select count(t.senderid) as nr
        from (
            select distinct on (mm.senderid) senderid  as senderid
            from msg_message mm
            where mm.recipientid = '21d8e305-6138-4afd-a0e5-ae2f23d7b4b6'
            and mm.read = false
            and mm.deleted = false
            group by mm.senderid
        ) as t
        * */

        String query = "select count(t.senderid) as nr"
            +" from ("
                +" select distinct on (mm.senderid) senderid  as senderid"
                +" from msg_message mm"
                +" where mm.recipientid = '"+backendUser.id+"'"
                +" and mm.read = false"
                +" and mm.deleted = false"
                +" group by mm.senderid"
            +" ) as t";

        MessageModel mm = new MessageModel();
        EntityManager em = mm.getEntityManager();
        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(query, Tuple.class).getResultList());

        return res;

        //return unreadNr;
    }

    @GET
    @Path("/unread-frontend")
    public Long unreadMessagesFrontend() {
        //id
        //f4359ab8-fc3e-44a6-b5bb-718d82d48f5e
        //userid
        //d0e0ce3c-24e1-42fd-bb0c-2200c7813351
        //String tmp = "d0e0ce3c-24e1-42fd-bb0c-2200c7813351";
        //UUID userId = UUID.fromString(tmp);

        UUID userId = UUID.fromString(token.getSubject());

        UserBasicInfoModel user = UserBasicInfoModel.find("userId", userId).firstResult();
        Long unreadNr = MessageModel.count("read=?1 and recipientId=?2 and deleted=?3", false, user.id, false);
        return unreadNr;
    }
}