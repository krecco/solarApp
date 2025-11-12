package org.bgr.service;

import net.bytebuddy.utility.RandomString;
import org.bgr.helper.ResultCommonObject;
import org.bgr.model.AuthUserVerify;
import org.bgr.model.db.FileModel;
import org.bgr.model.db.UserBasicInfoModel;

import javax.enterprise.context.ApplicationScoped;
import javax.transaction.Transactional;
import java.util.UUID;

@ApplicationScoped
public class StatusService {

    @Transactional
    public Long countPowerBillUploads(UUID plantId) {
        long count = FileModel.find("select id from FileModel where idFileContainer in (select id from FileContainerModel where contextType = 2 and contextId = '"+plantId.toString()+"' and type = 20)").count();
        return count;
    }

    @Transactional
    public Long countPlanDocumentUploads(UUID plantId) {
        long count = FileModel.find("select id from FileModel where idFileContainer in (select id from FileContainerModel where contextType = 2 and contextId = '"+plantId.toString()+"' and type = 22)").count();
        return count;
    }

    @Transactional
    public Long countPlanPhotos(UUID plantId) {
        long count = FileModel.find("select id from FileModel where idFileContainer in (select id from FileContainerModel where contextType = 3 and contextId = '"+plantId.toString()+"')").count();
        return count;
    }
}
