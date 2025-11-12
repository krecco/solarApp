package org.bgr.model;

import javax.validation.constraints.NotBlank;

public class UserDocumentUploadModel {
    @NotBlank(message = "type|not_blank")
    public Integer type;

    @NotBlank(message = "fileName|not_blank")
    public String fileName;

    @NotBlank(message = "file|not_blank")
    public String file;
}