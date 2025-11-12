package org.bgr.resource;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import io.quarkus.panache.common.Sort;
import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.helper.ResultCommonObject;
import org.bgr.model.api.DropdownModel;
import org.bgr.model.db.CampaignModel;
import org.bgr.model.db.ProjectUserModel;
import org.bgr.model.db.SettingsModel;
import org.eclipse.microprofile.jwt.JsonWebToken;

import javax.annotation.security.PermitAll;
import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;

@ApplicationScoped
@Path("settings")
public class AdminSettingsResource {
    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("list")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<SettingsModel> getList() {
        //return SettingsModel.findAll(Sort.by("active,t0").descending()).list();
        return SettingsModel.find("rs", Sort.by("active").descending().and("t0").descending(),1).list();
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("copy/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultCommonObject copySetting(@PathParam("id") UUID id) {

        SettingsModel setting = SettingsModel.findById(id);

        if (setting != null) {
            SettingsModel cloneSetting = new SettingsModel();

            cloneSetting.title = "Kopie - "+setting.title;
            cloneSetting.active = false;
            cloneSetting.rateConsumption = setting.rateConsumption;
            cloneSetting.rateExcessProduction = setting.rateExcessProduction;
            cloneSetting.rateExcessProductionExternal = setting.rateExcessProductionExternal;
            cloneSetting.kWpPrice = setting.kWpPrice;
            cloneSetting.kWpPriceSurcharge = setting.kWpPriceSurcharge;
            cloneSetting.planningFlatRateMin = setting.planningFlatRateMin;
            cloneSetting.planningFlatRate = setting.planningFlatRate;
            cloneSetting.planningFlatRateMax = setting.planningFlatRateMax;
            cloneSetting.serviceFeeMin = setting.serviceFeeMin;
            cloneSetting.serviceFee = setting.serviceFee;
            cloneSetting.serviceFeeMax = setting.serviceFeeMax;
            cloneSetting.interestRate = setting.interestRate;
            cloneSetting.spikePrice = setting.spikePrice;
            cloneSetting.surgePrice = setting.surgePrice;
            cloneSetting.pricePowerLanAdapter = setting.pricePowerLanAdapter;
            cloneSetting.kwpSizePewKw = setting.kwpSizePewKw;
            cloneSetting.moduleSize = setting.moduleSize;
            cloneSetting.buildingPermitCosts = setting.buildingPermitCosts;
            cloneSetting.subventionTo10Kw = setting.subventionTo10Kw;
            cloneSetting.subventionFrom10Kw = setting.subventionFrom10Kw;
            cloneSetting.directBuy = setting.directBuy;
            cloneSetting.rs = 1;

            cloneSetting.persist();

            return new ResultCommonObject(200, cloneSetting.id.toString());
       } else {
            return new ResultCommonObject(404, "NOT FOUND");
        }
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("add")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public SettingsModel addSetting(SettingsModel req) {
        req.active = false;
        req.rs = 1;
        req.persist();

        return req;
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("update-settings")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public SettingsModel editSetting(SettingsModel req) {

        try {
            SettingsModel settings = SettingsModel.findById(req.id);

            settings.rateConsumption = req.rateConsumption;
            settings.rateExcessProduction = req.rateExcessProduction;
            settings.rateExcessProductionExternal = req.rateExcessProductionExternal;
            settings.kWpPrice = req.kWpPrice;
            settings.kWpPriceSurcharge = req.kWpPriceSurcharge;
            settings.planningFlatRate = req.planningFlatRate;
            settings.planningFlatRateMin = req.planningFlatRateMin;
            settings.planningFlatRateMax = req.planningFlatRateMax;
            settings.serviceFee = req.serviceFee;
            settings.serviceFeeMin = req.serviceFeeMin;
            settings.serviceFeeMax = req.serviceFeeMax;
            settings.interestRate = req.interestRate;
            settings.spikePrice = req.spikePrice;
            settings.surgePrice = req.surgePrice;
            settings.pricePowerLanAdapter = req.pricePowerLanAdapter;
            settings.title = req.title;
            settings.active = req.active;
            settings.subventionTo10Kw = req.subventionTo10Kw;
            settings.subventionFrom10Kw = req.subventionFrom10Kw;
            settings.buildingPermitCosts = req.buildingPermitCosts;
            settings.kwpSizePewKw = req.kwpSizePewKw;
            settings.moduleSize = req.moduleSize;
            settings.subventionInstitution = req.subventionInstitution;
            settings.directBuy = req.directBuy;
            settings.kWpPriceSurchargeDirectBuy = req.kWpPriceSurchargeDirectBuy;

            settings.persist();

            return  settings;

        } catch (Exception e) {
            System.out.println(e);
            return  req;
        }
    }

    //default - will be extended // changed after created
    //@RolesAllowed({"manager", "admin"})
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-settings/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public SettingsModel getSettings(@PathParam("id") UUID id) {
        return SettingsModel.findById(id);
    }

    //do reflections
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-active-settings")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<SettingsModel> getActiveSettings() {
        List<SettingsModel> activeSettings = SettingsModel.find("active=?1 and rs=1", Sort.by("title").ascending(), true).list();
        return activeSettings;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-dropdown")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<DropdownModel> getDropdownList() {
        List<SettingsModel> list = SettingsModel.list("rs", Sort.by("title").ascending().and("active").descending(), 1);

        List<DropdownModel> res = new ArrayList<>();

        DropdownModel emptyObj = new DropdownModel();
        emptyObj.value = UUID.fromString("77399e89-79f4-470b-8eec-34e13ab9d74b");
        emptyObj.active = true;
        emptyObj.label = "Alle";
        res.add(emptyObj);

        for (SettingsModel et : list) {
            DropdownModel obj = new DropdownModel();
            obj.value = et.id;
            obj.active = et.active;
            obj.label = et.title;

            res.add(obj);
        }

        return res;
    }
}