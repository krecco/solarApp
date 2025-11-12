package org.bgr.resource;

import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.panache.common.Sort;
import io.quarkus.security.identity.SecurityIdentity;
import org.apache.commons.io.IOUtils;
import org.bgr.helper.FileHelper;
import org.bgr.helper.ParserHelper;
import org.bgr.helper.ResultCommonObject;
import org.bgr.helper.ResultHelper;
import org.bgr.model.*;
import org.bgr.model.db.*;
import org.bgr.service.KeycloakService;
import org.bgr.service.MailService;
import org.bgr.service.SolarPowerService;
import org.bgr.service.UserService;
import org.eclipse.microprofile.jwt.JsonWebToken;
import org.jboss.resteasy.plugins.providers.multipart.InputPart;
import org.jboss.resteasy.plugins.providers.multipart.MultipartFormDataInput;
import org.keycloak.representations.idm.UserRepresentation;

import javax.annotation.security.PermitAll;
import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.validation.ConstraintViolationException;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.MultivaluedMap;
import javax.ws.rs.core.Response;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.*;

@ApplicationScoped
@Path("project")
public class ProjectResource {

    /*
    *TODO
    * Access function to return if user can access data entry or not
    * verify:
    * user role > user (manger, admin)
    * auth user has role user and is user itself
    * user has access token for entry, defined with type and token
    * */

    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("add-user-project")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public String addUserToProject(ProjectUserModel pu) {

        System.out.println(pu.userId);
        System.out.println(pu.plantId);

        pu.persist();

        return "ok";
    }

}