package org.bgr.service;

import net.bytebuddy.utility.RandomString;
import org.apache.commons.lang3.StringUtils;
import org.bgr.helper.ResultCommonObject;
import org.bgr.model.*;
import org.bgr.model.db.UserBasicInfoModel;
import org.bgr.model.db.UserDataStatus;

import javax.enterprise.context.ApplicationScoped;
import javax.transaction.Transactional;
import javax.validation.Valid;
import java.util.UUID;

@ApplicationScoped
public class UserService {
    public void validateUserBasicInfo(@Valid UserBasicInfoModel user) {}

    @Transactional
    public ResultCommonObject addUserToVerificationTable(UserBasicInfoModel user, String type) {
        try {
            String token = RandomString.make(30);

            //token verification table
            AuthUserVerify registerVerification = new AuthUserVerify();
            registerVerification.userId = user.userId;
            registerVerification.email = user.email;
            registerVerification.token = token;
            registerVerification.type = type;
            registerVerification.rs = 0;

            registerVerification.persist();
            return new ResultCommonObject(200, token);
        } catch (Exception e) {
            return new ResultCommonObject(400, "AuthUserVerify FAIL");
        }
    }

    @Transactional
    public ResultCommonObject updateUser(UserBasicInfoModel userPost) {
        try {
            UserBasicInfoModel user = UserBasicInfoModel.find("id", userPost.id).firstResult();

            user.email = userPost.email;
            user.firstName = userPost.firstName;
            user.lastName = userPost.lastName;
            user.phoneNr = userPost.phoneNr;
            user.gender = userPost.gender;
            user.titlePrefix = userPost.titlePrefix;
            user.titleSuffix = userPost.titleSuffix;
            user.documentExtraTextBlockA = userPost.documentExtraTextBlockA;
            user.documentExtraTextBlockB = userPost.documentExtraTextBlockB;
            user.persist();

            checkBasicInfoData(userPost.id);

            return new ResultCommonObject(200, "User Update OK");
        } catch (Exception e) {
            System.out.println(e);
            return new ResultCommonObject(400, "User Update Fail");
        }
    }

    @Transactional
    public void checkBasicInfoData(UUID id) {
        UserBasicInfoModel user = UserBasicInfoModel.findById(id);
        Boolean dataOk = true;

        if ( (StringUtils.isEmpty(user.firstName)) || (StringUtils.isEmpty(user.lastName)) || (user.gender == null)  ) {
            dataOk = false;
        }

        UserDataStatus userStatus = UserDataStatus.find("userId", user.userId).firstResult();
        userStatus.basicInfo = dataOk;
        userStatus.persist();
    }

    @Transactional
    public void checkUserAddress(UUID id) {
        UserAddressModel address = UserAddressModel.findById(id);
        Boolean dataOk = true;

        if ( (StringUtils.isEmpty(address.street)) || (StringUtils.isEmpty(address.street)) || (StringUtils.isEmpty(address.street)) ) {
            dataOk = false;
        }


        UserDataStatus userStatus = UserDataStatus.find("userId", address.userId).firstResult();
        userStatus.addressInfo = dataOk;
        userStatus.persist();
    }

    @Transactional
    public void checkSepa(UUID id) {
        UserSepaPermissionModel sepa = UserSepaPermissionModel.findById(id);
        Boolean dataOk = true;

        if ( (StringUtils.isEmpty(sepa.account)) || (StringUtils.isEmpty(sepa.bic)) || (StringUtils.isEmpty(sepa.fullName)) ) {
            dataOk = false;
        }

        UserDataStatus userStatus = UserDataStatus.find("userId", sepa.userId).firstResult();
        userStatus.sepaInfo = dataOk;
        userStatus.persist();
    }

    @Transactional
    public void checkPowerBill(UUID id) {
        //deprecaTED!
        /*
        SolarPlantPowerBill bill = SolarPlantPowerBill.findById(id);

        Boolean dataOk = true;

        if ( (StringUtils.isEmpty(bill.provider)) || (StringUtils.isEmpty(bill.netProvider)) || (StringUtils.isEmpty(bill.contract)) ||
             (StringUtils.isEmpty(bill.billNo)) || (StringUtils.isEmpty(bill.billPeriod)) ||
             (bill.consumption < 1)
        ) {
            dataOk = false;
        } */


        //todo update
        //UserDataStatus userStatus = UserDataStatus.find("userId", bill.userId).firstResult();

        //userStatus.powerBillInfo = dataOk;
        //userStatus.persist();
    }
}
