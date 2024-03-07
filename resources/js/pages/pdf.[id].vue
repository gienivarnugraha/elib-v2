<template>
  <div>
    <!-- used `style="height: 100vh;"` because without it in the Firefox 89 and Chrome 91 (June 2021) the `vue-pdf-app` is not rendering on the page, just empty space without any errors (since `vue-pdf-app` does not have height and it is the top tag in the generated markup ) -->
    <!-- or you can just wrap `vue-pdf-app` in <div> tag and set height for it via CSS (like in `Script tag (unpkg)` example below) -->
    <ADialog v-model="showModal">
      <ACard title="Input Password" subtitle="Ask the librarian for password"
        class="a-dialog backface-hidden transform translate-z-0" @keydown.esc="showModal = false">
        <!-- ℹ️ Recursively pass down slots to child -->
        <template #header-right>
          <ABtn icon="i-bx-x" variant="light" icon-only color="primary" v-tooltip="'Close'"
            class="rounded-full text-xs h-8 w-8" @click="$router.back()" />
        </template>

        <div class="pa-4 text-center">
          <AInput v-model="passcode" label="Passcode" :error="errorMessage" placeholder="Enter passcode"></AInput>
          <span class="h-separator my-4"></span>
          <ABtn class="w-50 text-center " @click="validatePasscode()" color="red"> OK
          </ABtn>
        </div>

      </ACard>
    </ADialog>

    <vue-pdf-app v-if="pass" style="height: 100vh;" :config="config" :pdf="pdf"></vue-pdf-app>

  </div>
</template>

<script setup>
import VuePdfApp from "vue3-pdf-app";
// import this to use default icons for buttons
import "vue3-pdf-app/dist/icons/main.css";

const route = useRoute()

const resourceId = route.params.id

const showModal = ref(true)

const passcode = ref('')

const pass = ref(false)

let pdf = ref()

let errorMessage = ref('')

/**
 * Validates a passcode by making a POST request to the '/api/orders/validate' endpoint.
 *
 * @param {type} paramName - description of parameter
 * @return {type} description of return value
 */
const validatePasscode = async () => {
  try {
    const { data } = await Application.request().post(`/api/orders/show`, {
      uuid: resourceId,
      passcode: passcode.value
    }, {
      responseType: 'arraybuffer'
    })

    pass.value = true

    pdf.value = data

    showModal.value = false

  } catch (error) {
    //console.error(error.response.data)
    const err = JSON.parse(new TextDecoder().decode(error.response.data))

    errorMessage.value = err.message
  }
}

const config = {
  toolbar: {
    toolbarViewerRight: {
      presentationMode: false,
      openFile: false,
      print: false,
      download: false,
      viewBookmark: false,
    },
  },
  secondaryToolbar: {
    secondaryPresentationMode: false,
    secondaryOpenFile: false,
    secondaryPrint: false,
    secondaryDownload: false,
    secondaryViewBookmark: false,
    cursorSelectTool: false,
  },
}

</script>

<route lang="json">
{
  "name": "pdf-viewer",
  "meta": {
    "layout": "pdf",
    "requiresAuth": true
  }
}
</route>