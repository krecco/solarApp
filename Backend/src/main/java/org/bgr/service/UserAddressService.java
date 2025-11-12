package org.bgr.service;

import org.bgr.model.UserAddressModel;

import javax.enterprise.context.ApplicationScoped;
import javax.validation.Valid;

@ApplicationScoped
public class UserAddressService {
    public void validateUserAddress(@Valid UserAddressModel address) {}
}