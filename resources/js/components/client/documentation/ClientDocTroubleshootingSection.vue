<script setup lang="ts"></script>

<template>
  <div class="prose prose-slate dark:prose-invert max-w-none">
    <h1>Troubleshooting</h1>
    <div class="space-y-4">
      <div class="rounded-md bg-slate-100 p-4 dark:bg-slate-800">
        <h3 class="text-slate-900 dark:text-slate-100">1. Youtube Video Troubleshooting</h3>
        <p class="text-slate-900 dark:text-slate-100">
          If you're having trouble with youtube video, please follow the steps
          in bellow
          <ul>
            <li>
              <p>Go to chrome extentions <span><a href="https://chromewebstore.google.com/detail/get-cookiestxt-locally/cclelndahbckbenkjhflpdbgdldlbecc" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400" >Get Cookies.txt Locally</a></span></p>
            </li>
            <li>
              <p>Install the extension</p>
            </li>
            <li>
              <p>Open the youtube page <span><a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400" >https://www.youtube.com/</a></span></p>
            </li>
            <li>
              <p>Make sure you are logged in youtube account in the current browser</p>
            </li>
            <li>
              <p>In current youtube home page, open the extension in the toolbar by clicking on the extension icon in the browser toolbar.</p>
            </li>
            <li>
              <p>Click on the "Export Cookies" button in the extension popup.</p>
            </li>
            <li>
              <p>Save the cookies.txt file to your computer.</p>
            </li>
            <li>
              <p>Make sure the contents of the .txt file only contain cookies from the youtube.com domain.</p>
            </li>
            <li>
              <p>Add the cookies.txt content to the <span class="text-green-600 dark:text-green-400">deployment/docker/volumes/config/youtube-cookies.txt</span> file.</p>
            </li>
            <li>
              <p>Restart the app instance service by running the following command in the terminal from the root directory of the project:</p>
            </li>
            <li>
              <p>docker compose --env-file .env -f deployment/docker/docker-compose.yml restart app</p>
            </li>
          </ul>
        </p>
      </div>
    </div>
  </div>
</template>
