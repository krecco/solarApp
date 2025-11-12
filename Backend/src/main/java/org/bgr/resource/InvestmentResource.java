package org.bgr.resource;

import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.panache.common.Sort;
import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.helper.ResultCommonObject;
import org.bgr.helper.ResultHelper;
import org.bgr.model.api.RepaymentTransactionModel;
import org.bgr.model.db.InvestmentModel;
import org.bgr.model.db.InvestmentRepaymentModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.bgr.service.MailService;
import org.eclipse.microprofile.jwt.JsonWebToken;

import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.enterprise.inject.Produces;
import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;

@ApplicationScoped
@Path("investment")
public class InvestmentResource {

    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @Inject
    MailService mailService;

    @GET
    @Path("list")
    @RolesAllowed({"manager", "admin"})
    @Transactional
    public InvestmentResult getList(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy,
                @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("status") Integer status) {

        return new InvestmentResult(200, InvestmentModel.count("rs IS NULL OR rs < 99"),InvestmentModel.listInvestors(sortBy, descending, page, perPage, q,
                status));
    }

    @GET
    @Path("investment-calculation/{investmentId}")
    @RolesAllowed({"user", "manager", "admin"})
    @Transactional
    public List<InvestmentRepaymentModel> getInvesmentCaclulation(@PathParam("investmentId") UUID investmentId) {

        InvestmentModel investment = InvestmentModel.findById(investmentId);
        UserBasicInfoModel user = UserBasicInfoModel.findById(investment.userId);

        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ArrayList<>();
            }
        }

        return InvestmentRepaymentModel.list("rs=?1 and investmentId=?2", Sort.by("year").ascending(), 1, investmentId);
    }

    @POST
    @Path("repayment-transaction/")
    //skip for now
    //@RolesAllowed({"manager", "admin"})
    @Transactional
    public ResultCommonObject processRepaymentTransaction(RepaymentTransactionModel repayment) {
        InvestmentRepaymentModel investmentRepayment = InvestmentRepaymentModel.findById(repayment.repaymentId);
        InvestmentModel investment = InvestmentModel.findById(investmentRepayment.investmentId);


        //do real logic when input data is known
        investmentRepayment.repaid = true;
        investmentRepayment.repaidTxId = repayment.repaidTxId;
        investmentRepayment.repaidDate = repayment.repaidDate;

        investmentRepayment.persist();

        mailService.investmentRepaymentNotification(investment.userId, investmentRepayment);

        return new ResultCommonObject(200, "OK");
    }

    @GET
    @Path("delete-investment/{investmentId}")
    @Transactional
    public ResultCommonObject deleteInvestment(@PathParam("investmentId") UUID investmentId) {
        InvestmentModel investment = InvestmentModel.findById(investmentId);

        investment.rs = 99;
        investment.persist();

        return new ResultCommonObject(200,"OK");
    }

    class InvestmentResult extends ResultHelper {
        public long records = -1;

        public InvestmentResult(Integer status, Long records, List<ObjectNode> investmentList) {
            super();
            this.records = records;
            this.status = status;
            this.payload = investmentList;
        }
    }
}
