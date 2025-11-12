package org.bgr.resource;

import org.bgr.helper.MessagingPayloadBuilder;
import org.bgr.helper.MessagingResponseBuilder;
import org.bgr.model.MessageModel;
import org.bgr.model.ProfileModel;
import org.bgr.model.ReactionModel;

import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.UUID;

@Path("/message")
public class MessagingReactionResource {

    @POST
    @Path("/add-reaction/{messageId}/{contactId}")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response addReaction(@PathParam("messageId") UUID messageId, @PathParam("contactId") UUID contactId, ReactionModel reactionModel) {
        try{
            reactionModel.message = MessageModel.find("id = ?1", messageId).firstResult();
            reactionModel.contactId = contactId;
            reactionModel.persist();
            return  MessagingResponseBuilder.Build(200,
                    new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok, null, null));
        }
        catch (Exception e){
            return MessagingResponseBuilder.Build(500,
                    new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.InternalServerError, null, null));
        }
    }

    @DELETE
    @Path("/delete-reaction-by-id/{reactionId}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response deleteReactionById(@PathParam("reactionId") UUID reactionId) {
        long result = ReactionModel.delete("id = ?1", reactionId);
        return result > 0 ? MessagingResponseBuilder.Build(200, new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.Ok, null, null)) :
                MessagingResponseBuilder.Build(404, new MessagingPayloadBuilder<ProfileModel>().buildPayload(MessagingResponseBuilder.StatusCode.NotFound, null, null));
    }
}