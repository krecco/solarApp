package org.bgr.service;

import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.core.MultivaluedMap;
import javax.ws.rs.core.UriInfo;

import org.bgr.model.AttachmentModel;
import org.bgr.model.MessageModel;
import org.bgr.resource.MessagingMessageResource;
import org.apache.commons.io.IOUtils;
import org.eclipse.microprofile.config.inject.ConfigProperty;
import org.jboss.logging.Logger;
import org.jboss.resteasy.plugins.providers.multipart.InputPart;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.UUID;

import static java.lang.System.getProperty;

@ApplicationScoped
public class MessageService {
    @ConfigProperty(name = "app.folder.messaging")
    String FILE_FOLDER;

    private static final Logger logger = Logger.getLogger(MessageService.class);

    @Inject
    private AttachmentModel attachmentRepository;

    public Set<String> saveAttachments(List<InputPart> files) throws IOException {
        Set<String> savedFilePaths = new HashSet<>();
        try {
            for (InputPart inputPart : files) {
                MultivaluedMap<String, String> header = inputPart.getHeaders();
                String fileName = new File(FILE_FOLDER) + File.separator + UUID.randomUUID() + '_' + getFileName(header);
                saveFile(inputPart, fileName);
                savedFilePaths.add(fileName);
            }
        } catch (Exception e){
            System.out.println(e);
            logger.debug(e.getMessage());
        }
        return savedFilePaths;
    }

    @Transactional
    public AttachmentModel getAttachment(UUID id) {
        return attachmentRepository.find("id = ?1", id).firstResult();
    }

    public void saveFile(InputPart inputPart, String fileName) throws IOException {
        InputStream inputStream = inputPart.getBody(InputStream.class, null);
        byte[] bytes = IOUtils.toByteArray(inputStream);
        Files.write(Paths.get(fileName), bytes, StandardOpenOption.CREATE_NEW);
    }

    public String getFileName(MultivaluedMap<String, String> header) {
        String[] contentDisposition = header.getFirst("Content-Disposition").split(";");
        for (String filename : contentDisposition) {
            if (filename.trim().startsWith("filename")) {
                String[] name = filename.split("=");
                return name[1].trim().replaceAll("\"", "");
            }
        }
        return "unknown";
    }

    public MessageModel getMessageHATEOAS(MessageModel message, UriInfo uriInfo) {
        MessageModel msg = message;
        msg.addLink(getSelfLink(msg, uriInfo), "self", null, null);
        addAttachmentLinks(uriInfo, msg);
        return msg;
    }

    public void addAttachmentLinks(UriInfo uriInfo, MessageModel msg) {
        msg.getAttachments().forEach(attachment -> msg.addLink(getAttachmentLink(attachment, uriInfo), "attachments", getAttachmentFileType(attachment.fileLocation), getAttachmentFilename(attachment.fileLocation)));
    }

    private String getAttachmentFileType(String fileLocation) {
        String fileName = getAttachmentFilename(fileLocation);
        return fileName.substring(fileName.lastIndexOf(".") + 1);
    }

    private String getAttachmentFilename(String fileLocation) {
        File f = new File(fileLocation);
        return f.getName();
    }

    public String getAttachmentLink(AttachmentModel attachment, UriInfo uriInfo) {
        return uriInfo.getBaseUriBuilder()
                .path(MessagingMessageResource.class)
                .path("/attachment")
                .path(String.valueOf(attachment.getId()))
                .build()
                .toString();
    }

    public String getSelfLink(MessageModel msg, UriInfo uriInfo) {
        return uriInfo.getBaseUriBuilder()
                .path(MessagingMessageResource.class)
                .path(String.valueOf(msg.getId()))
                .build()
                .toString();
    }
}
