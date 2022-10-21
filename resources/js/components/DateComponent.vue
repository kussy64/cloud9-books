<template>
  <div id="app">
  <v-app> 
    <v-container fluid>
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
              @input="showReceiptDateStart = false"
              @change="handleSettingEndDate(formSearch.receipt_date_start, 'receipt_date_end')"
            ></v-date-picker>
        <div class="custom-between">
          〜
        </div>
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
    </v-container>
</v-app>
</div>
</template>

<script>
import moment from 'moment';
   export default {
  computed: {
    computedReceiptDateStart() {
      return this.formSearch.receipt_date_start ? moment(this.formSearch.receipt_date_start).format('YYYY年MM月DD日') : ''
    },
    computedReceiptDateEnd() {
      return this.formSearch.receipt_date_end ? moment(this.formSearch.receipt_date_end).format('YYYY年MM月DD日') : ''
    },
    computedCreateDateStart() {
      return this.formSearch.create_date_start ? moment(this.formSearch.create_date_start).format('YYYY年MM月DD日') : ''
    },
    computedCreateDateEnd() {
      return this.formSearch.create_date_end ? moment(this.formSearch.create_date_end).format('YYYY年MM月DD日') : ''
    },
    computedScheduledDateStart() {
      return this.formSearch.scheduled_date_start ? moment(this.formSearch.scheduled_date_start).format('YYYY年MM月DD日') : ''
    },
    computedScheduledDateEnd() {
      return this.formSearch.scheduled_date_end ? moment(this.formSearch.scheduled_date_end).format('YYYY年MM月DD日') : ''
    },
    computedAssessmentDateStart() {
      return this.formSearch.assessment_date_start ? moment(this.formSearch.assessment_date_start).format('YYYY年MM月DD日') : ''
    },
    computedAssessmentDateEnd() {
      return this.formSearch.assessment_date_end ? moment(this.formSearch.assessment_date_end).format('YYYY年MM月DD日') : ''
    },
    isdisabledButtonExport(){
      let exist =[3,9].indexOf((+this.formSearch.voucher_type))===-1?false:true;
      return exist;
    },
    loading(){
      const{loadingOrderConstructionList,loadingOrderSectionList,loadingOrderCustomerList,loadingOrderSupplierList, loadingOrderAccountList, loadingOrderSubAccountList,loadingSupplierTypeListMaster,loadingGetStaffListBySectionCode}=this.loadingOver;
      this.loadingOver.value =loadingOrderConstructionList || loadingOrderSectionList || loadingOrderCustomerList || loadingOrderSupplierList || loadingOrderAccountList || loadingOrderSubAccountList || loadingSupplierTypeListMaster || loadingGetStaffListBySectionCode;
      this.$emit('loadingValue',this.loadingOver.value);
      return this.loadingOver.value;
    },
    isDisabledButtonApproval() {
      return this.currentRoleApprovalForOrder == 1;
    },
  }
   };
</script>