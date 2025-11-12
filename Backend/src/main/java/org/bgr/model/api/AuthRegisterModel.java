package org.bgr.model.api;

import javax.validation.constraints.Email;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

public class AuthRegisterModel extends AuthLoginModel {

    @NotBlank(message = "firstName|not_blank")
    public String firstName;

    @NotBlank(message = "lastName|not_blank")
    public String lastName;
}
