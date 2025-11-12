package org.bgr.model.api;

import javax.validation.constraints.Email;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

public class AuthLoginModel {
    @Email(message = "email|not_valid")
    @NotBlank(message = "email|not_blank")
    public String username;

    @Size(min = 6, message = "password|to_short_{min}")
    @NotBlank(message = "password|not_blank")
    public String password;
}