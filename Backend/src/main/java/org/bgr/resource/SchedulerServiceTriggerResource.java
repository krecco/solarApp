package org.bgr.resource;

import org.bgr.service.SchedulerBackendService;

import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import java.util.UUID;

@ApplicationScoped
@Path("scheduler-service")
public class SchedulerServiceTriggerResource {

    @Inject
    SchedulerBackendService schedulerBackendService;
    /*
    @GET
    @Path("process-user-type")
    public void processUserType() {
        schedulerBackendService.setUserType();
    }

    @GET
    @Path("process-customer-type")
    public void processCustomerType() {
        schedulerBackendService.processCustomerType();
    }

    @GET
    @Path("process-dashboard-data")
    public void processDashboardData() {
        schedulerBackendService.processDashboardData();
    }

     */

    @GET
    @Path("process-dashboard-data/{key}")
    public void processDashboardData(@PathParam("key") UUID key) {
        UUID checkKey = UUID.fromString("5fd873f0-11cf-4e28-9f3e-9cb42e4d3199");
        if (key.equals(checkKey)) {
            schedulerBackendService.processDashboardData();
        }
    }
}
