package org.bgr.helper;

public class ResultCommonObject {

    private String message;
    private Integer status;

    public ResultCommonObject(Integer status, String message) {
        this.status = status;
        this.message = message;
    }

    public String getMessage() {
        return message;
    }

    public Integer getStatus() {
        return status;
    }
}