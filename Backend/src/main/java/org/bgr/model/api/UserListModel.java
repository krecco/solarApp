package org.bgr.model.api;

import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.UUID;

public class UserListModel {
    public UUID id;
    public UUID userId;
    public String email;
    public String firstName;
    public String lastName;
    public String phoneNr;
    public Integer gender;
    public String titlePrefix;
    public String titleSuffix;
    public Boolean isBusiness;
    public Boolean isInvestor;
    public Boolean isPlantOwner;
    public Integer customerType;
    public LocalDateTime t0;
    public Integer customerNo;
    public String street;
    public String city;
    public String postNr;
}