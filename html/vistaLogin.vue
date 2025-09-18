<template>
  <v-app>
    <v-app-bar color="primary" dark>
      <v-toolbar-title>PARQUEADERO LA 64</v-toolbar-title>
      <v-spacer />
      <v-btn icon>
        <v-icon>mdi-account</v-icon>
      </v-btn>
    </v-app-bar>

    <v-navigation-drawer app>
      <v-list>
        <v-subheader>UBICACION</v-subheader>
        <v-list-item>
          <v-img src="ubicacion.png" aspect-ratio="1"></v-img>
        </v-list-item>
        <v-subheader>CONTACTOS</v-subheader>
        <v-list-item>
          <v-icon color="green">mdi-whatsapp</v-icon>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="4">
            <v-card>
              <v-tabs>
                <v-tab>LOGIN</v-tab>
                <v-tab>REGISTER</v-tab>
              </v-tabs>
              <v-card-text>
                <v-text-field label="CORREO"></v-text-field>
                <v-text-field label="CONTRASEÑA" type="password"></v-text-field>
              </v-card-text>
              <v-card-actions>
                <!-- Redirige a otro.html al dar click -->
                <v-btn color="primary" block href="./vistaprincipal.html">INGRESAR</v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>
