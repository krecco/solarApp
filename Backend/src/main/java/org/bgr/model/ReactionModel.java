package org.bgr.model;

import com.fasterxml.jackson.annotation.JsonBackReference;
import com.fasterxml.jackson.annotation.JsonIgnore;
import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.util.UUID;

@Entity
@Table(name="msg_reaction", indexes = {
        @Index(columnList = "id", name = "ndx_msg_reaction_id"),
        @Index(columnList = "value", name = "ndx_msg_reaction_value"),
        @Index(columnList = "contactId", name = "ndx_msg_reaction_contact_id"),
}
)

public class ReactionModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public String value;

    public UUID contactId;

    @JsonIgnore
    @JsonBackReference
    @ManyToOne
    public MessageModel message;
}
