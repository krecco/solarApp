package org.bgr.helper;

import com.openhtmltopdf.pdfboxout.PdfRendererBuilder;
import com.openhtmltopdf.svgsupport.BatikSVGDrawer;

import java.io.FileOutputStream;
import java.io.OutputStream;

public class HtmlToPdfConverter {
    public boolean convertHtmlFileToPdf(String source, String destination) {
        try {
            OutputStream os = new FileOutputStream(destination);
            PdfRendererBuilder builder = new PdfRendererBuilder();
            builder.useFastMode();
            builder.withUri(source);
            builder.toStream(os);
            builder.useSVGDrawer(new BatikSVGDrawer()); // Load SVG support plugin
            builder.run();

            return true;
        } catch (Exception e) {
            System.out.println(e);
            return false;
        }
    }
}
