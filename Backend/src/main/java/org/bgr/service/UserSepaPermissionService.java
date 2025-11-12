package org.bgr.service;

import org.bgr.model.UserSepaPermissionModel;

import javax.enterprise.context.ApplicationScoped;
import javax.validation.Valid;

@ApplicationScoped
public class UserSepaPermissionService {
    public void validateSepa(@Valid UserSepaPermissionModel sepa) {}
}