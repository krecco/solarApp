package org.bgr.resource;

import io.quarkus.panache.common.Sort;
import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.model.api.DropdownModel;
import org.bgr.model.db.CampaignModel;
import org.bgr.model.db.SettingsModel;
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
@Path("campaign")
public class CampaignSettingsResource {
    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("list")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<CampaignModel> getList() {
        return CampaignModel.find("rs", Sort.by("active").descending().and("t0").descending(),1).list();
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("add")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public CampaignModel add(CampaignModel req) {
        req.active = false;
        req.rs = 1;
        req.persist();

        return req;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-settings/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public CampaignModel getSettings(@PathParam("id") UUID id) {
        return CampaignModel.findById(id);
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("update-settings")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public CampaignModel editSetting(CampaignModel req) {

        try {
            CampaignModel settings = CampaignModel.findById(req.id);
            settings.title = req.title;
            settings.active = req.active;

            settings.persist();

            return  settings;
        } catch (Exception e) {
            System.out.println(e);
            return  req;
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-active-settings")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<CampaignModel> getActiveSettings() {
        List<CampaignModel> activeSettings = CampaignModel.find("active", Sort.by("title").ascending(), true).list();
        return activeSettings;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-dropdown")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<DropdownModel> getDropdownList() {
        List<CampaignModel> list = CampaignModel.list("rs", Sort.by("title").ascending().and("active").descending(), 1);

        List<DropdownModel> res = new ArrayList<>();

        DropdownModel emptyObj = new DropdownModel();
        emptyObj.value = UUID.fromString("77399e89-79f4-470b-8eec-34e13ab9d74b");
        emptyObj.active = true;
        emptyObj.label = "Alle";
        res.add(emptyObj);

        for (CampaignModel et : list) {
            DropdownModel obj = new DropdownModel();
            obj.value = et.id;
            obj.active = et.active;
            obj.label = et.title;

            res.add(obj);
        }

        return res;
    }
}