package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

@Entity
@Table(name="scheduler", indexes = {
        @Index(columnList = "id", name = "ndx_scheduler_id"),
        @Index(columnList = "t0", name = "ndx_scheduler_t0"),
        @Index(columnList = "rs", name = "ndx_scheduler_rs")
    }
)
public class SchedulerModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public String function;

    @Column(name="arguments")
    @ElementCollection(targetClass=String.class)
    public List<String> arguments;

    public UUID contextId;

    @CreationTimestamp
    public LocalDateTime t0;

    @ColumnDefault("0")
    public Integer rs;
}
