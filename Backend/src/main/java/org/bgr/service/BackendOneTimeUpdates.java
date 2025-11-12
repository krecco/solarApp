package org.bgr.service;

import org.bgr.model.SolarPlantModel;
import org.bgr.model.db.FileContainerModel;

import javax.enterprise.context.ApplicationScoped;
import javax.transaction.Transactional;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@ApplicationScoped
public class BackendOneTimeUpdates {

    @Transactional
    public void insertMissingPlantFileContainers() {
        System.out.println(" ");
        System.out.println("****************************************insertMissingPlantFileContainers start ****************************************");

        //missing definition --- preferred way doing one by one
        /*
        //SEPA
        fc.contextType = 2;
        fc.type = 201;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 9;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        */

        //change definitions accordingly
        Integer contextType = 2;
        Integer type = 201;
        Integer sortOrder = 9;
        Boolean uploadOnly = false;
        Boolean downloadOnly = false;
        Boolean backendUploadOnly = false;
        Boolean backendOnly = false;
        Boolean noStatusUpdate = false;
        Integer rs = 1;

        //get all solar plants
        List<SolarPlantModel> plantList = SolarPlantModel.listAll();

        for (SolarPlantModel plant: plantList) {
            System.out.println("--------------------------------------------------------------------------------------");
            System.out.println("Checking plant: "+plant.title);

            Map<String, Object> params = new HashMap<>();
            params.put("contextType", contextType);
            params.put("type", type);
            params.put("plantId", plant.id);

            Long nr = FileContainerModel.count("contextId= :plantId and contextType= :contextType and type= :type", params);
            System.out.println("Number of valid containers found: "+nr+" | needed 1");

            if (nr < 1) {
                System.out.println("Inserting new object ...");

                FileContainerModel fc = new FileContainerModel();
                fc.contextType = contextType;
                fc.type = type;
                fc.contextId = plant.id;
                fc.rs = rs;
                fc.sortOrder = sortOrder;
                fc.uploadOnly = uploadOnly;
                fc.downloadOnly = downloadOnly;
                fc.backendUploadOnly = backendUploadOnly;
                fc.backendOnly = backendOnly;
                fc.noStatusUpdate = noStatusUpdate;

                //insert object
                fc.persist();

                System.out.println("Inserting new object ... finished");
            }


            System.out.println("Plant update finished!");
        }

        System.out.println("****************************************insertMissingPlantFileContainers finished ****************************************");
    }
}
