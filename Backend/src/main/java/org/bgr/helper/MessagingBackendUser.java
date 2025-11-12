package org.bgr.helper;

import java.util.UUID;

public class    MessagingBackendUser {
    public UUID id;
    public String fullName;
    public String avatar;
    public String avatarBg;

    public MessagingBackendUser(String _id, String _fullName, String _avatar, String _avatarBg) {
        id = UUID.fromString(_id);
        fullName = _fullName;
        avatar = _avatar;
        avatarBg = _avatarBg;
    }

    public static MessagingBackendUser getMessagingBackendUser() {
        return new MessagingBackendUser("21d8e305-6138-4afd-a0e5-ae2f23d7b4b6", "solar.family", "SF", "primary");
    }
}
