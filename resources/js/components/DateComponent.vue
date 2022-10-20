<template>
  <div id="app">
  <v-app>    
      <v-row>
            
              <v-text-field
                :value="computedReceiptDateStart"
                @click:clear="formSearch.receipt_date_start = null"
                clearable
                outlined
                dense
                label="伝票日付(開始)"
                prepend-icon=""
                readonly
                v-bind="attrs"
                v-on="on"
                class="custom-picker"
              ></v-text-field>
            <v-date-picker
              v-model="formSearch.receipt_date_start"
              locale="ja"
              :allowed-dates="allowedReceiptDateStart"
            ></v-date-picker>
              <v-text-field
                :value="computedReceiptDateEnd"
                @click:clear="formSearch.receipt_date_end = null"
                clearable
                outlined
                dense
                label="伝票日付(終了)"
                prepend-icon=""
                readonly
                v-bind="attrs"
                v-on="on"
                class="custom-picker"
              ></v-text-field>
            <v-date-picker
              v-model="formSearch.receipt_date_end"
              locale="ja"
              :allowed-dates="allowedReceiptDateEnd"
              @input="showReceiptDateEnd = false"
            ></v-date-picker>

      </v-row>
</v-app>
</div>
</template>
