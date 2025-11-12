package org.bgr.model;

import java.util.UUID;

public class Reaction {

    public Reaction(UUID id, UUID messageId, String value, UUID contactId){
        this.id = id;
        this.messageId = messageId;
        this.value = value;
        this.contactId = contactId;
    }
    public UUID id;
    public UUID messageId;
    public UUID contactId;
    public String value;
}
