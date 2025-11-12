package org.bgr.helper;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;
import org.bgr.model.db.UserBasicInfoModel;

import javax.enterprise.context.ApplicationScoped;
import javax.validation.ConstraintViolation;
import javax.validation.ConstraintViolationException;

@ApplicationScoped
public class ParserHelper {
    public ObjectNode parseConstraintViolationException(ConstraintViolationException violations) {

        ObjectMapper mapper = new ObjectMapper();
        ObjectNode node = mapper.createObjectNode();

        for (ConstraintViolation<?> violation : violations.getConstraintViolations()) {
            String[] violationArr = violation.getMessage().toString().split("\\|");

            node.put(violationArr[0], violationArr[1]);
        }

        return node;
    }
}