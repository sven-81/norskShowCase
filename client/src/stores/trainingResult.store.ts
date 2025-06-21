import {defineStore} from 'pinia';

function random(smileys: string[]) {
    return smileys[Math.floor(Math.random() * smileys.length)];
}

export const useResultStore = defineStore({
    id: 'results',
    state: () => ({
        result: {
            message: '',
            type: ''
        },
    }),
    actions: {
        success() {
            const smileys = [
                '😊',
                '😄',
                '👍',
                '🙌',
                '🎉',
                '😁',
                '😀',
                '😃',
                '😉',
                '😅',
                '😆',
                '😍',
                '😎',
                '😲',
                '😮',
                '😲',
                '🤓',
                '🤠',
                '🤩',
                '🥳',
                '👏',
                '👌',
                '🤝',
            ];
            const randomSmiley = random(smileys);
            this.result = {message: 'Alles richtig! ' + randomSmiley, type: 'correct'};
        },
        error: function (validationMessage) {
            const smileys = [
                '😢',
                '😏',
                '😐',
                '😑',
                '😒',
                '😓',
                '😔',
                '😕',
                '😖',
                '😞',
                '😟',
                '😣',
                '😥',
                '😦',
                '😧',
                '😨',
                '😩',
                '😪',
                '😫',
                '😬',
                '😭',
                '😯',
                '😱',
                '😵',
                '😶',
                '😳',
                '🙄',
                '🙁',
                '🤔',
                '🤕',
                '🤣',
                '🤢',
                '🤪',
                '🤫',
                '🤭',
                '🥱',
                '🤯',
                '🥺',
                '🧐',
                '🙈',
                '🙈🙉🙊',
            ];
            const randomSmiley = random(smileys);
            this.result = {
                message: '<div>Oh no ' + randomSmiley + '</div>'
                    + '<div>' + validationMessage + '</div>',
                type: 'mistake'
            };
        },
        clear() {
            this.result = {
                message: '',
                type: ''
            };
        }
    }
});