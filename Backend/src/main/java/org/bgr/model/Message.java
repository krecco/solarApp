package org.bgr.model;

import java.util.List;
import java.util.UUID;

public class Message {
    public Message(Integer _id, UUID _userId, UUID _contactId, Integer _unseenMsgs, List<MessageModel> _messages, List<Reaction> _reactions){
        id = _id;
        userId = _userId;
        contactId = _contactId;
        unseenMsgs = _unseenMsgs;
        messages = _messages;
        reactions = _reactions;
    }
    public Integer id;
    public UUID userId;
    public UUID contactId;
    public Integer unseenMsgs;
    public List<MessageModel> messages;
    public List<Reaction> reactions;
}
