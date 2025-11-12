package org.bgr.helper;

import com.sun.istack.Nullable;

public class MessagingApiResponse<T> {

    public MessagingApiResponse(T _payload, @Nullable Integer _statusCode, String _errorMessage){
        payload = _payload;
        statusCode = _statusCode;
        errorMessage = _errorMessage;
    }

    public T payload;
    public Integer statusCode;
    public String errorMessage;
}