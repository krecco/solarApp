package org.bgr.helper;

public class MessagingPayloadBuilder<T> {

    public MessagingApiResponse<T> buildPayload(MessagingResponseBuilder.StatusCode statusCode, T payload, String message){
       switch (statusCode){
           case Ok:
               return new MessagingApiResponse<>(payload, 200, null);
           case BadRequest:
               return new MessagingApiResponse<>(null, 400, message);
           case NotFound:
               return new MessagingApiResponse<>(null, 404, "Entity with provided key/id not found");
           case Unauthorized:
               return  new MessagingApiResponse<>( null, 401, "Unauthorized");
           case Forbidden:
               return  new MessagingApiResponse<>( null, 403, "Forbidden");
           case InternalServerError:
               return  new MessagingApiResponse<>( null, 500, message != null ? message : "Internal server error");
           default:
               return  new MessagingApiResponse<>( null, 500, "Response type not implemented");
       }
    }
}