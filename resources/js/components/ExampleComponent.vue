<template>
  <div id="app">
  <v-app>
    <v-container fluid>
      <v-row
        align="center"
      >
          <v-autocomplete
          v-model="model"
          type="text"
          name="keyword"
          :value="$keyword"
          :items="items"
            outlined
            
          >
          </v-autocomplete>
          </v-row>
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
      </v-row>

    </v-container>
    </v-app>
    </div>
</template>

<script>
import axios from 'axios';
  export default {
      data() {
        return {
        model:"",
        items: [
          'PHP基礎参考',
          'SQL参考書',
          "SQL参考書php",
          "PHP基礎参考書",
        ],
        
      };
      },
        mounted() {
            axios.get('/').then(response => this.books = response.data);
        }
    };
</script>