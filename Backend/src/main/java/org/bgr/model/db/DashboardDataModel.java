package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="dashboard_data_v2", indexes = {
        @Index(columnList = "id", name = "ndx_dashboard_data_id_v2"),
        @Index(columnList = "t0", name = "ndx_dashboard_data_t0_v2"),
    }
)
public class DashboardDataModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public Long plantForecastSent;
    public Long plantContractSent;
    public Long plantContractFinalized;
    public Long plantInstalled;
    public Long investmentContractSent;
    public Long investmentContractFinalized;
    public Long userNr;
    public Long webInfoNr;
    public Long plantInUseNr;

    @CreationTimestamp
    public LocalDateTime t0;

    @ColumnDefault("1")
    public Integer rs;
}
