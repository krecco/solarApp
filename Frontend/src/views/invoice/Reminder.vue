<template>
  <div>
    <b-card>
      <b-card-body>
        <b-row>
          <b-col
            cols="12"
            md="12"
          >
            <br><br>
            <b-table
              striped
              responsive
              :hover="true"
              :items="invoiceList"
              class="mb-0"
            >
              <template
                #cell(Status)="data"
              >
                <b-badge
                  v-if="data.value==1"
                  pill
                  variant="success"
                >
                  paid
                </b-badge>
                <b-badge
                  v-if="data.value==0"
                  pill
                  variant="warning"
                >
                  open
                </b-badge>

              </template>
              <template
                #cell(action)="data"
              >
                <div
                  class="text-right"
                >
                  <a
                    :href="`http://localhost:8090/fakeFiles/${data.value}`"
                    target="_blank"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                  </a>
                </div>
              </template>
            </b-table>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
  </div>
</template>

<script>
import {
  BRow, BCol, BTable, BCard, BCardBody, BBadge,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BRow, BCol, BTable, BCard, BCardBody, BBadge,
  },
  directives: {
    Ripple,
  },
  setup() {
    //  define fieldnames in production
    const invoiceList = [
      {
        InvoiceNo: '2021-2806',
        PlantNo: 66311,
        Status: 0,
        Sum: '$3,737.93',
        User: 'Pruitt Kaufman',
        Address: '433 Underhill Avenue, Lafferty, Washington, 4605',
        DueDate: '2018-02-11',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-5671',
        PlantNo: 97028,
        Status: 0,
        Sum: '$2,019.15',
        User: 'Kari Camacho',
        Address: '822 Bulwer Place, Urbana, Utah, 3456',
        DueDate: '2014-02-18',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-3370',
        PlantNo: 32774,
        Status: 1,
        Sum: '$1,757.55',
        User: 'Mary Mcconnell',
        Address: '219 Vandam Street, Walton, Rhode Island, 7809',
        DueDate: '2015-11-19',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-7321',
        PlantNo: 94667,
        Status: 0,
        Sum: '$1,299.77',
        User: 'Kent Carver',
        Address: '418 Clinton Avenue, Rote, Indiana, 7940',
        DueDate: '2020-04-25',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-6833',
        PlantNo: 26783,
        Status: 0,
        Sum: '$1,373.31',
        User: 'Justine Ballard',
        Address: '996 Williams Court, Bagtown, Nebraska, 1575',
        DueDate: '2016-07-01',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-2501',
        PlantNo: 92971,
        Status: 0,
        Sum: '$1,780.79',
        User: 'Haley Riley',
        Address: '995 Richmond Street, Walland, Florida, 3565',
        DueDate: '2020-03-11',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
    ]

    return {
      invoiceList,
    }
  },
}
</script>

<style>

</style>
