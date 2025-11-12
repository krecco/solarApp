package org.bgr.helper;

import io.quarkus.qute.Template;
import javax.enterprise.context.Dependent;
import javax.inject.Inject;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;

@Dependent
public class HtmlDocumentGenerator {
    @Inject
    Template document_header;

    @Inject
    Template document_header_no_logo;

    @Inject
    Template document_footer;

    public String getDocumentHeader(String subHeader, Boolean showLogo)  {
        try{
            String issiueDate = LocalDate.now().format(DateTimeFormatter.ofPattern("dd.MM. yyyy"));
            if (showLogo == false) {
                return document_header_no_logo.data("issueDate", issiueDate).render();
            } else {
                return document_header.data("subHeader", subHeader).data("issueDate", issiueDate).render();
            }
        } catch (Exception e) {
            return "";
        }
    }

    public String getDocumentFooter()  {
        try{
            return document_footer.render();
        } catch (Exception e) {
            return "";
        }
    }

    public String generateHtmlDoc(String body) {
        String subHeader = "";
        String doc = getDocumentHeader(subHeader, true);
        doc += body;
        doc += getDocumentFooter();

        return doc;
    }

    public String generateHtmlDoc(String body, String subHeader) {
        String doc = getDocumentHeader(subHeader, true);
        doc += body;
        doc += getDocumentFooter();

        return doc;
    }

    public String generateHtmlDoc(String body, String subHeader, Boolean showLogo) {
        String doc = getDocumentHeader(subHeader, showLogo);
        doc += body;
        doc += getDocumentFooter();

        return doc;
    }
}
