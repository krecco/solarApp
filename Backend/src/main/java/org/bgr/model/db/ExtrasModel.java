package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="extras", indexes = {
        @Index(columnList = "id", name = "ndx_extras_id"),
        @Index(columnList = "t0", name = "ndx_extras_t0"),
        @Index(columnList = "rs", name = "ndx_extras_rs"),
        @Index(columnList = "active", name = "ndx_extras_active")
    }
)
public class ExtrasModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public String title;
    public String ean;
    public Double price;

    public Boolean active;
    @CreationTimestamp
    public LocalDateTime t0;

    public Integer rs;
}