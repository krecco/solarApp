package org.bgr.helper;

import javax.enterprise.context.Dependent;
import javax.ws.rs.core.MultivaluedMap;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;

@Dependent
public class FileHelper {
    public boolean saveToDisk(String folder, String fileName, String content) {
        try {
            byte[] strToBytes = content.getBytes();
            File uploadFolder = new File(folder);
            FileOutputStream fos = new FileOutputStream(uploadFolder + "/" + fileName);
            fos.write(strToBytes);
            fos.close();

            return true;
        } catch (Exception e) {
            return false;
        }
    }

    public String getFileName(MultivaluedMap<String, String> header) {
        String[] contentDisposition = header.getFirst("Content-Disposition").split(";");

        for (String filename : contentDisposition) {
            if ((filename.trim().startsWith("filename"))) {

                String[] name = filename.split("=");

                String finalFileName = name[1].trim().replaceAll("\"", "");
                return finalFileName;
            }
        }
        return "unknown";
    }

    //todo remove tika extension!
    public String getFileType(MultivaluedMap<String, String> header) {
        try {
            String contentType = header.getFirst("Content-Type");
            return contentType;
        } catch (Exception e) {
            return "unknown";
        }
    }

    public void writeFile(byte[] content, String filename) throws IOException {
        File file = new File(filename);

        if (!file.exists()) {
            file.createNewFile();
        }

        FileOutputStream fop = new FileOutputStream(file);

        fop.write(content);
        fop.flush();
        fop.close();
    }
}
