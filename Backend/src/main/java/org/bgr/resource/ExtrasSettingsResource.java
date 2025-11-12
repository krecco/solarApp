package org.bgr.resource;

import io.quarkus.panache.common.Sort;
import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.helper.GermanNumberParser;
import org.bgr.model.api.DropdownModel;
import org.bgr.model.api.ExtrasDropdownModel;
import org.bgr.model.db.CampaignModel;
import org.bgr.model.db.ExtrasModel;
import org.bgr.model.db.SolarPlantExtrasModel;
import org.eclipse.microprofile.jwt.JsonWebToken;

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
@Path("extras")
public class ExtrasSettingsResource {
    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("list")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<ExtrasModel> getList() {
        return ExtrasModel.find("rs", Sort.by("active", Sort.Direction.Descending).and("title", Sort.Direction.Ascending),1).list();
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("add")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ExtrasModel add(ExtrasModel req) {
        req.active = true;
        req.rs = 1;
        req.persist();

        return req;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-settings/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ExtrasModel getSettings(@PathParam("id") UUID id) {
        return ExtrasModel.findById(id);
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("update-settings")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ExtrasModel editSetting(ExtrasModel req) {

        try {
            ExtrasModel settings = ExtrasModel.findById(req.id);
            settings.title = req.title;
            settings.active = req.active;
            settings.ean = req.ean;
            settings.price = req.price;

            settings.persist();

            return  settings;
        } catch (Exception e) {
            System.out.println(e);
            return  req;
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-active-dropdown")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<ExtrasDropdownModel> getActiveDropdownList() {
        List<ExtrasModel> list = ExtrasModel.find("rs=?1 and active=?2", Sort.by("active", Sort.Direction.Descending).and("title", Sort.Direction.Ascending),1,true).list();
        List<ExtrasDropdownModel> res = new ArrayList<>();

        for (ExtrasModel et : list) {
            ExtrasDropdownModel obj = new ExtrasDropdownModel();
            obj.value = et.id;
            obj.active = et.active;
            obj.label = et.title+" | "+ GermanNumberParser.getGermanCurrencyFormat(et.price,2);
            obj.price = et.price;

            res.add(obj);
        }

        return res;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-active-dropdown-values/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<ExtrasDropdownModel> getActiveDropdownValues(@PathParam("id") UUID id) {

        List<SolarPlantExtrasModel> list = SolarPlantExtrasModel.find("plantId=?1 and active=?2", Sort.by("title", Sort.Direction.Ascending),id,true).list();
        List<ExtrasDropdownModel> res = new ArrayList<>();

        for (SolarPlantExtrasModel et : list) {
            ExtrasDropdownModel obj = new ExtrasDropdownModel();
            obj.value = et.extrasId;
            obj.active = et.active;
            obj.label = et.title+" | "+ GermanNumberParser.getGermanCurrencyFormat(et.price,2);
            obj.price = et.price;

            res.add(obj);
        }

        return res;
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("map-to-plant/{id}")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Boolean addToPlant(@PathParam("id") UUID id, List<UUID> req) {
        SolarPlantExtrasModel.delete("plantid", id);

        List<ExtrasModel> extrasList = ExtrasModel.find("rs=?1 and active=?2", Sort.by("active", Sort.Direction.Descending).and("title", Sort.Direction.Ascending),1,true).list();
        for (ExtrasModel et : extrasList) {

            SolarPlantExtrasModel spe =  new SolarPlantExtrasModel();
            spe.extrasId = et.id;
            spe.plantId = id;
            spe.title = et.title;
            spe.price = et.price;
            spe.qt = 1.0;

            spe.active = false;
            for (UUID reqId : req) {
                if (reqId.equals(et.id)) {
                    spe.active = true;
                }
            }
            spe.persist();
        }

        //update after confirm
        return true;
    }
}