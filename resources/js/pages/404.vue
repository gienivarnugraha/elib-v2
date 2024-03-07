<template>
  <Layout>
    <div class="h-screen w-screen flex flex-col flex-wrap content-center justify-center">
      <div class="flex flex-col items-center">
        <Icon icon="bx-question-mark" class="h-20 w-20 text-primary-300 border rounded-full" />
        <h1 class="mt-6 mb-2 text-3xl font-bold text-neutral-900 dark:text-white">
          Oops!
        </h1>
        <h2 class="text-2xl text-neutral-700 dark:text-neutral-100">
          Page not found
        </h2>
      </div>

      <div class="flex flex-col justify-center">
        <p class="mt-5 text-neutral-700 dark:text-neutral-300">
          The page you are trying to view does not exists or you don't have
          permissions to access it.
        </p>
        <div class="mt-5 text-center">
          <ABtn icon="bx-chevron-left" to="/" size="lg">Back</ABtn>
        </div>
      </div>
    </div>
  </Layout>
</template>
<script>
export default {
  created() {

  },
}
</script>

<route lang="json">
{
  "name": "404",
  "meta": {
    "title": "404"
  }
}
</route>