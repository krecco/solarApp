package org.bgr.model;

import com.fasterxml.jackson.annotation.JsonIgnore;
import org.bgr.helper.Link;
import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;
import java.util.Set;
import java.util.UUID;

@Entity
@Table(name="msg_message", indexes = {
        @Index(columnList = "id", name = "ndx_msg_message_id"),
        @Index(columnList = "t0", name = "ndx_msg_message_t0"),
        @Index(columnList = "senderId", name = "ndx_msg_message_senderId"),
        @Index(columnList = "recipientId", name = "ndx_msg_message_recipientId"),
        @Index(columnList = "read", name = "ndx_msg_message_read"),
        @Index(columnList = "edited", name = "ndx_msg_message_edited"),
        @Index(columnList = "deleted", name = "ndx_msg_message_deleted"),
}
)
public class MessageModel extends PanacheEntityBase {
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

    @NotBlank(message = "content|not_blank")
    @Column(name="message", columnDefinition="TEXT")
    public String message;

    /* DB create test
    @Column(name="message1", columnDefinition="TEXT")
    public String message1;
     */

    public UUID senderId;

    public UUID keycloakId;

    public UUID recipientId;

    public Boolean read;

    public Boolean smtpDelivered;

    public Boolean edited;

    public Boolean deleted;

    @JsonIgnore
    @OneToMany(mappedBy = "message",
            cascade = CascadeType.ALL,
            orphanRemoval = true)
    private Set<AttachmentModel> attachments;

    @JsonIgnore
    @OneToMany(mappedBy = "message",
            cascade = CascadeType.ALL,
            orphanRemoval = true, fetch = FetchType.EAGER)
    private Set<ReactionModel> reactions;

    @Transient
    List<Link> links = new ArrayList<>();

    @Transient
    public List<Reaction> messageReactions = new ArrayList<>();

    public MessageModel() {
    }

    public MessageModel(String message) {
        this.message = message;
    }

    public MessageModel(String message, Set<AttachmentModel> attachments, UUID senderId, UUID recipientId, UUID keycloakId) {
        this.message = message;
        this.attachments = attachments;
        this.senderId = senderId;
        this.recipientId = recipientId;
        this.keycloakId = keycloakId;
        this.read = false;
        this.smtpDelivered = false;
        this.edited = false;
        this.deleted = false;
    }

    public void addLink(String url, String rel, String type, String name) {
        Link link = new Link(url, rel, type, name);
        links.add(link);
    }

    public void addReaction(ReactionModel reactionModel){
        messageReactions.add(new Reaction(reactionModel.id, reactionModel.message.id, reactionModel.value, reactionModel.contactId));
    }

    public UUID getId() {
        return id;
    }

    public UUID getSenderId() {
        return senderId;
    }

    public void setSenderId(UUID senderId) {
        this.senderId = senderId;
    }

    public UUID getRecipientId() {
        return recipientId;
    }

    public void setRecipientId(UUID recipientId) {
        this.recipientId = recipientId;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public Set<AttachmentModel> getAttachments() {
        return attachments;
    }

    public Set<ReactionModel> getReactions() {
        return reactions;
    }

    public void setAttachments(Set<AttachmentModel> attachments) {
        this.attachments = attachments;
    }

    public List<Link> getLinks() {
        return links;
    }

    public void setLinks(List<Link> links) {
        this.links = links;
    }
}
