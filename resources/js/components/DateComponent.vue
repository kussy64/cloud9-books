<template>
  <div id="app">
  <v-app> 
    <v-container fluid>
          <v-row>
            <v-menu
    ref="menu"
    v-model="menu"
    :close-on-content-click="false"
    :return-value.sync="targetDate"
    min-width="auto"
  >
    <template #activator="{ on, attrs }">
      <v-text-field
        v-model="targetDate"
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
    </template>
    <v-date-picker
      v-model="targetDate"
      locale="ja"
      @input="
        $refs.menu.save(targetDate)
        menu = false
      "
    >
    </v-date-picker>
  </v-menu>
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