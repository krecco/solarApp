package org.bgr.helper;

import org.bgr.model.Message;
import org.bgr.model.MessageModel;

import java.util.ArrayList;
import java.util.List;
import java.util.UUID;
import java.util.stream.Collectors;

public class MessagingMessageHelper {
    public static List<Message> GetMessages(List<MessageModel> messageModels, UUID userId){
        //TODO: Refactor and split function to make it more clear what is going on and split into single responsibility functions
        List<Message> messages = new ArrayList<>();
        messageModels.forEach(messageModel -> {
            Message msg = messages.stream().filter(x -> x.contactId.equals(messageModel.recipientId != userId ? messageModel.recipientId : messageModel.senderId)).findFirst().orElse(null);
            if(msg == null && ! messageModels.stream().filter(x -> x.senderId.equals(userId) && x.recipientId.equals(messageModel.recipientId != userId ? messageModel.recipientId : messageModel.senderId) ||
                    x.recipientId.equals(userId) && x.senderId.equals(messageModel.recipientId == userId ? messageModel.senderId : messageModel.recipientId)).collect(Collectors.toList()).isEmpty()){

                messages.add(new Message(messageModels.indexOf(messageModel), userId, messageModel.recipientId != userId ? messageModel.recipientId : messageModel.senderId,
                        messageModels.stream().filter(x -> x.senderId.equals(userId) && x.recipientId.equals(messageModel.recipientId != userId ? messageModel.recipientId : messageModel.senderId) && !x.read ||
                                x.recipientId.equals(userId) && x.senderId.equals(messageModel.recipientId == userId ? messageModel.senderId : messageModel.recipientId)  && !x.read).collect(Collectors.toList()).size(),
                        messageModels.stream().filter(x -> x.senderId.equals(userId) && x.recipientId.equals(messageModel.recipientId != userId ? messageModel.recipientId : messageModel.senderId) ||
                                x.recipientId.equals(userId) && x.senderId.equals(messageModel.recipientId == userId ? messageModel.senderId : messageModel.recipientId)).collect(Collectors.toList()), messageModel.messageReactions));
            }
        });
        return messages;
    }
}