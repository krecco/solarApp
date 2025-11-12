package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.util.UUID;

@Entity
@Table(name="msg_contact", indexes = {
        @Index(columnList = "id", name = "ndx_msg_contact_id"),
        @Index(columnList = "belongsTo", name = "ndx_msg_contact_belongsTo"),
        @Index(columnList = "belongsTo", name = "ndx_msg_contact_contactId"),
}
)

public class ContactModel  extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID belongsTo;

    public UUID contactId;
}
