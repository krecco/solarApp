package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="solar_plant_extras", indexes = {
        @Index(columnList = "extrasId", name = "ndx_solar_plant_extras_extrasId"),
        @Index(columnList = "plantId", name = "ndx_solar_plant_extras_plantId")
    }
)
public class SolarPlantExtrasModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;
    public UUID plantId;
    public UUID extrasId;
    public String title;
    public Double price;
    public Double qt;
    public Boolean active;
    @CreationTimestamp
    public LocalDateTime t0;
}