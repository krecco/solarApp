package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import java.util.UUID;

@Entity
@Table(name="msg_profile", indexes = {
        @Index(columnList = "id", name = "ndx_msg_profile_id"),
        @Index(columnList = "fullName", name = "ndx_msg_profile_fullName"),
        @Index(columnList = "avatar", name = "ndx_msg_profile_avatar"),
        @Index(columnList = "avatarBg", name = "ndx_msg_profile_avatarBg"),
}
)

public class ProfileModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    @NotBlank(message = "fullName|not_blank")
    public String fullName;

    public String avatar;

    public String avatarBg;
}
