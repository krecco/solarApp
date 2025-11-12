package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.UUID;

// Plan was to have multiple power_plants within one project, this is dismissed for now.
// One user, one power plant (project_user naming convention is left as we will move back to initial state).

@Entity
@Table(name="project_user", indexes = {
        @Index(columnList = "id", name = "ndx_project_user_id"),
        @Index(columnList = "t0", name = "ndx_project_user_t0"),
        @Index(columnList = "userId", name = "ndx_project_user_userId"),
        @Index(columnList = "plantId", name = "ndx_project_user_plantId"),
        @Index(columnList = "rs", name = "ndx_project_user_rs"),
    }
)
public class ProjectUserModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    @CreationTimestamp
    public LocalDateTime t0;

    public UUID userId;
    public UUID plantId;
    public Integer rs;

    /*
    public Double initialAmount;
    public Double openAmount;
    public Double repaymentRateAmount;
    public Integer repaymentIntervalInMonths;
    public LocalDate repaymentStartDate;
    public LocalDate repaymentEndDate;
    public LocalDate repaymentLastIssuedBillDate;
    public Integer totalRatesNr;
    */

    //add fields later
}
