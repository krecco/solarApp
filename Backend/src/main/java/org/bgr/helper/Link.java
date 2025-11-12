package org.bgr.helper;

import java.io.Serializable;

public class Link implements Serializable {
    private final String url;
    private final String rel;
    private final String type;
    private final String name;

    public Link(String url, String rel, String fileType, String fileName) {
        this.url = url;
        this.rel = rel;
        this.name = fileName;
        this.type = fileType;
    }

    public String getUrl() {
        return url;
    }

    public String getRel() {
        return rel;
    }

    public String getType() {
        return type;
    }

    public String getName() {
        return name;
    }
}