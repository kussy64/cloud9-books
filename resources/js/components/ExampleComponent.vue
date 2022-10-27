<template>
  <div id="app">
  <v-app>
    <v-container fluid>
      <v-row
        align="center"
      >
        <template>
          <v-autocomplete
          v-model="model"
          type="text"
          :items="items"
          name="keyword"
          :value="$keyword"
          outlined
          >
          </v-autocomplete>
          </template>
          </v-row>
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
                @click:clear="formSearch.receipt_date_start = null"
                clearable
                outlined
                dense
                label="日付(開始)"
                prepend-icon=""
                readonly
                v-bind="attrs"
                v-on="on"
                name="date"
                :value="$date"
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
          <v-row>
            <v-menu
    ref="menu2"
    v-model="menu2"
    :close-on-content-click="false"
    :return-value.sync="targetDate2"
    min-width="auto"
  >
    <template #activator="{ on, attrs }">
                        <v-text-field
                v-model="targetDate2"
                @click:clear="formSearch.receipt_date_end = null"
                clearable
                outlined
                dense
                label="日付(終了)"
                prepend-icon=""
                readonly
                v-bind="attrs"
                v-on="on"
                name="enddate"
                :value="$enddate"
                class="custom-picker"
              ></v-text-field>
    </template>
    <v-date-picker
      v-model="targetDate2"
      locale="ja"
      @input="
        $refs.menu2.save(targetDate2)
        menu2 = false
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