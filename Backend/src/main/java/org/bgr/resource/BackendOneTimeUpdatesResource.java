package org.bgr.resource;

import org.bgr.service.BackendOneTimeUpdates;
import org.bgr.service.SchedulerBackendService;

import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import java.util.UUID;

@ApplicationScoped
@Path("updates")
public class BackendOneTimeUpdatesResource {

    @Inject
    BackendOneTimeUpdates backendOneTimeUpdates;

    @Inject
    SchedulerBackendService schedulerBackendService;


    @GET
    @Path("plant-file-containers/{key}")
    public void insertMissingPlantFileContainers(@PathParam("key") UUID key) {
        UUID checkKey = UUID.fromString("5fd873f0-11cf-4e28-9f3e-9cb42e4d3199");

        if (key.equals(checkKey)) {
            backendOneTimeUpdates.insertMissingPlantFileContainers();
        }
    }
}
