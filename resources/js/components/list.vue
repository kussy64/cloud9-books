<template>
  <div id="app">
    <v-container fluid>
      <v-row
        align="center"
      >
        <v-col cols="12">
          <v-autocomplete
            
          :items="items"
            outlined
            multiple
            type="text"
          >

</v-autocomplete>
        </v-col>
      </v-row>
    </v-container>
    </div>
</template>

<script>
  export default {
      data: () => ({
        model: "",
        items: [
          "PHP",
          "Laravel",
          "MySql",
          "HTML",
          "JavaScript",
          
        ],
      }),
    };
</script>