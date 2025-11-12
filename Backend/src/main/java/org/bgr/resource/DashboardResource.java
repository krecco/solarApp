package org.bgr.resource;

import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.panache.common.Sort;
import org.bgr.model.db.DashboardDataModel;

import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.transaction.Transactional;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import java.util.List;

@ApplicationScoped
@Path("dash")
public class DashboardResource {

    @GET
    //@RolesAllowed({"manager", "admin"})
    @Path("stats")
    @Transactional
    public List<DashboardDataModel> getStats() {
        PanacheQuery<DashboardDataModel> stats = DashboardDataModel.find("rs", Sort.by("t0").descending(), 1);
        stats.range(0, 30);
        return stats.list();
    }
}
