package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.NotNull;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="user_document", indexes = {
        @Index(columnList = "id", name = "ndx_user_document_id"),
        @Index(columnList = "userId", name = "ndx_user_document_userId"),
        @Index(columnList = "type", name = "ndx_user_document_type"),
        @Index(columnList = "t0", name = "ndx_user_document_t0")
}
)
public class UserDocumentModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    @Column(unique = true)
    public UUID userId;

    @NotNull(message = "type|not_null")
    public Integer type;

    @NotBlank(message = "fileName|not_blank")
    public String fileName;

    public String folder;

    @CreationTimestamp
    public LocalDateTime t0;
}