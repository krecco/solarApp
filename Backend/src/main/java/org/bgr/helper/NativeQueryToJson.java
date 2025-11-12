package org.bgr.helper;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;

import javax.enterprise.context.ApplicationScoped;
import javax.persistence.Tuple;
import javax.persistence.TupleElement;
import java.util.ArrayList;
import java.util.List;

@ApplicationScoped
public class NativeQueryToJson {

    public List<ObjectNode> toJsonArray(List<Tuple> results) {

        List<ObjectNode> json = new ArrayList<ObjectNode>();

        ObjectMapper mapper = new ObjectMapper();

        //todo change sql null to ""
        for (Tuple t : results)
        {
            List<TupleElement<?>> cols = t.getElements();

            ObjectNode one = mapper.createObjectNode();

            try {
                for (TupleElement col : cols)
                {
                    if (t.get(col.getAlias()) != null) {
                        one.put(col.getAlias(), t.get(col.getAlias()).toString());
                    }
                }

                json.add(one);
            } catch (Exception e) {
                System.out.println("Parsing NativeQueryToJson");
                System.out.println(e);
            }
        }

        return json;
    }
}