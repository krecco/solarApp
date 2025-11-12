package org.bgr.helper;

import io.quarkus.qute.Template;

import javax.enterprise.context.Dependent;
import javax.inject.Inject;

@Dependent
public class HtmlMailGenerator {
    @Inject
    Template mail_header;

    @Inject
    Template mail_footer;

    public String getDocumentHeader()  {
        try{
            return mail_header.render();
        } catch (Exception e) {
            return "";
        }
    }

    public String getDocumentFooter()  {
        try{
            return mail_footer.render();
        } catch (Exception e) {
            return "";
        }
    }

    public String generateHtmlMail(String body) {
        String doc = getDocumentHeader();
        doc += body;
        doc += getDocumentFooter();

        return doc;
    }
}
