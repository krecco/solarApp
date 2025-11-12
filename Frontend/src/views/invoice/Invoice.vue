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
        InvoiceNo: '2021-6457',
        PlantNo: 69216,
        Status: 1,
        Sum: '$1,477.03',
        User: 'Delacruz Wilkerson',
        Address: '163 Sackman Street, Fillmore, Texas, 7423',
        DueDate: '2014-10-15',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-2651',
        PlantNo: 27223,
        Status: 1,
        Sum: '$3,065.61',
        User: 'Hazel Gomez',
        Address: '713 Bowne Street, Holtville, New Hampshire, 8327',
        DueDate: '2015-02-22',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-6134',
        PlantNo: 53609,
        Status: 1,
        Sum: '$2,043.81',
        User: 'Stout Lloyd',
        Address: '769 Plaza Street, Riegelwood, Alaska, 576',
        DueDate: '2020-01-01',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-4811',
        PlantNo: 90082,
        Status: 1,
        Sum: '$3,637.27',
        User: 'Stephanie Mcguire',
        Address: '864 Debevoise Avenue, Kenwood, Idaho, 1279',
        DueDate: '2017-11-06',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-4419',
        PlantNo: 87630,
        Status: 0,
        Sum: '$2,629.33',
        User: 'Christi Kemp',
        Address: '939 Indiana Place, Sims, Minnesota, 7749',
        DueDate: '2016-06-24',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
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
        Status: 0,
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
      {
        InvoiceNo: '2021-5686',
        PlantNo: 71464,
        Status: 1,
        Sum: '$3,909.45',
        User: 'Gordon Whitney',
        Address: '742 Newton Street, Madaket, West Virginia, 9596',
        DueDate: '2017-07-15',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-8874',
        PlantNo: 17943,
        Status: 1,
        Sum: '$1,680.72',
        User: 'Renee Finch',
        Address: '742 Chester Avenue, Lodoga, New Mexico, 7875',
        DueDate: '2015-02-14',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-2977',
        PlantNo: 96682,
        Status: 1,
        Sum: '$1,566.71',
        User: 'Key Stokes',
        Address: '863 Georgia Avenue, Stollings, Connecticut, 6733',
        DueDate: '2015-07-17',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-5863',
        PlantNo: 66040,
        Status: 0,
        Sum: '$1,788.55',
        User: 'Klein Carlson',
        Address: '423 Russell Street, Moscow, Missouri, 9582',
        DueDate: '2015-01-06',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-5049',
        PlantNo: 46095,
        Status: 1,
        Sum: '$2,276.01',
        User: 'Cooke Lyons',
        Address: '526 Rockaway Parkway, Ventress, Palau, 7119',
        DueDate: '2015-05-12',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-4927',
        PlantNo: 15350,
        Status: 0,
        Sum: '$1,155.70',
        User: 'Madeline Stanton',
        Address: '375 Strickland Avenue, Bergoo, Maryland, 9683',
        DueDate: '2015-08-06',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-2579',
        PlantNo: 83480,
        Status: 0,
        Sum: '$2,931.64',
        User: 'Melva Hewitt',
        Address: '284 Lincoln Road, Ypsilanti, Georgia, 6270',
        DueDate: '2016-11-28',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-5211',
        PlantNo: 53835,
        Status: 1,
        Sum: '$2,953.67',
        User: 'Herrera Kelly',
        Address: '754 Metrotech Courtr, Tivoli, Vermont, 9674',
        DueDate: '2019-05-29',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-6106',
        PlantNo: 88515,
        Status: 1,
        Sum: '$2,484.33',
        User: 'Sweeney Stone',
        Address: '761 Irvington Place, Kilbourne, South Dakota, 7570',
        DueDate: '2015-06-29',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-1205',
        PlantNo: 98649,
        Status: 0,
        Sum: '$2,532.19',
        User: 'Melody Farmer',
        Address: '611 Madoc Avenue, Sterling, Alabama, 7649',
        DueDate: '2018-07-20',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-8988',
        PlantNo: 19877,
        Status: 0,
        Sum: '$3,239.09',
        User: 'Richards Pruitt',
        Address: '359 Pioneer Street, Juarez, New York, 1195',
        DueDate: '2015-11-06',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
      {
        InvoiceNo: '2021-5197',
        PlantNo: 43430,
        Status: 1,
        Sum: '$3,043.78',
        User: 'Aurora Rodriguez',
        Address: '611 Bradford Street, Sunriver, Iowa, 2779',
        DueDate: '2015-05-25',
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
