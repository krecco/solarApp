package org.bgr.helper;

import com.fasterxml.jackson.databind.node.ObjectNode;

public class ResultHelper {

    public Integer status;
    public Object payload;

    public ResultHelper() {}

    public ResultHelper(Integer status, ObjectNode node) {
        this.status = status;
        this.payload = node;
    }

    public ResultHelper(Integer status, ResultCommonObject resultCommonObject) {
        this.status = status;
        this.payload = resultCommonObject;
    }

    public ResultHelper(ResultCommonObject resultCommonObject) {
        this.payload = resultCommonObject;
    }

    public Integer getStatus() {
        return status;
    }

    public Object getPayload() {
        return payload;
    }
}