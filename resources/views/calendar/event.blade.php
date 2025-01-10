<div class="flex flex-col items-start">
    <span x-text="event.title"></span>
    <template x-for="user in event.extendedProps.users">
        <span x-text="user.name" class="font-semibold">asdf</span>
    </template>
</div>
