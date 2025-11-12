package org.bgr.helper;

import javax.ws.rs.core.Response;

public class MessagingResponseBuilder {

    private MessagingResponseBuilder(){}

    public static Response Build(int statusCode, Object object){
        return Response.status(statusCode).entity(object).build();
    }

    public enum StatusCode{
        Ok,
        NotFound,
        Forbidden,
        BadRequest,
        Unauthorized,
        InternalServerError,
    }
}