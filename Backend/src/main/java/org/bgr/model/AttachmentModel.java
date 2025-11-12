package org.bgr.model;

import com.fasterxml.jackson.annotation.JsonBackReference;
import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.enterprise.context.ApplicationScoped;
import javax.persistence.*;
import java.io.File;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="msg_attachment", indexes = {
        @Index(columnList = "id", name = "ndx_msg_attachment_id"),
        @Index(columnList = "t0", name = "ndx_msg_attachment_t0"),
}
)

@ApplicationScoped
public class AttachmentModel extends PanacheEntityBase {
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

    public String fileLocation;

    public String fileExtension;

    public String fileName;

    @Transient
    private File file;

    @JsonBackReference
    @ManyToOne
    private MessageModel message;

    public AttachmentModel() {
    }

    public AttachmentModel(String fileLocation, MessageModel message) {
        this.fileLocation = fileLocation;
        this.message = message;
    }

    public UUID getId() {
        return id;
    }

    public String getFileLocation() {
        return fileLocation;
    }

    public void setFileLocation(String fileLocation) {
        this.fileLocation = fileLocation;
    }

    public MessageModel getMessage() {
        return message;
    }

    public void setMessage(MessageModel message) {
        this.message = message;
    }
}
