import React, {
    useCallback,
    useContext,
    useEffect,
    useRef,
    useState
} from 'react';
import SVG from 'react-inlinesvg';
import { PLAYERSTATUS } from '~enum/PlayerStatus';
import { MusicContext } from '~Contexts/MusicContext';
import Slider from '@material-ui/core/Slider';
import CurrentPlaying from '~components/CurrentPlaying';
import CurrentPlayingArt from '~components/CurrentPlayingArt';
import SimpleRating from '~components/SimpleRating';
import { AuthKey } from '~logic/Auth';
import { flagSong } from '~logic/Song';
import { toast } from 'react-toastify';
import SliderControl from '~components/MusicControl/components/SliderControl';

import style from './index.styl';

interface MusicControlProps {
    authKey: AuthKey;
}

const MusicControl: React.FC<MusicControlProps> = (props) => {
    const musicContext = useContext(MusicContext);

    const [ratingToggle, setRatingToggle] = useState(false);

    const [currentTime, setCurrentTime] = useState('');

    const handleRatingToggle = () => {
        if (musicContext.currentPlayingSong) {
            setRatingToggle(!ratingToggle);
        }
    };

    const formatLabel = (s) => [
        (s - (s %= 60)) / 60 + (9 < s ? ':' : ':0') + s
        //https://stackoverflow.com/a/37770048
    ];

    const handleFlagSong = (songID: string, favorite: boolean) => {
        flagSong(songID, favorite, props.authKey)
            .then(() => {
                musicContext.flagCurrentSong(favorite);
                if (favorite) {
                    return toast.success('Song added to favorites');
                }
                toast.success('Song removed from favorites');
            })
            .catch(() => {
                if (favorite) {
                    toast.error(
                        '😞 Something went wrong adding song to favorites.'
                    );
                } else {
                    toast.error(
                        '😞 Something went wrong removing song from favorites.'
                    );
                }
            });
    };

    return (
        <div
            className={`${style.musicControl} ${
                ratingToggle ? style.ratingShown : null
            }`}
        >
            <CurrentPlayingArt />

            <CurrentPlaying />
            <div className={style.ratingBarContainer}>
                <div className={style.ratingBar}>
                    <SimpleRating
                        value={musicContext.currentPlayingSong?.rating}
                        fav={musicContext.currentPlayingSong?.flag}
                        itemID={musicContext.currentPlayingSong?.id}
                        setFlag={handleFlagSong}
                    />
                </div>
            </div>

            <SliderControl />
            <div className={style.seekTimes}>
                <span className={style.seekStart}>{currentTime}</span>
                <span className={style.seekEnd}>
                    {formatLabel(musicContext.currentPlayingSong?.time ?? 0)}
                </span>
            </div>

            <div className={style.controls}>
                <div className={style.previousSong}>
                    <SVG
                        src={require('~images/icons/svg/previous-track.svg')}
                        title='Previous'
                        description='Play previous song'
                        role='button'
                        aria-disabled={musicContext.songQueueIndex <= 0}
                        onClick={() => {
                            musicContext.playPrevious();
                        }}
                        className={`
                            icon icon-button 
                            ${
                                musicContext.songQueueIndex <= 0
                                    ? style.disabled
                                    : ''
                            }
                        `}
                    />
                </div>
                <div className={style.playPause}>
                    {musicContext.playerStatus === PLAYERSTATUS.STOPPED ||
                    musicContext.playerStatus === PLAYERSTATUS.PAUSED ? (
                        <SVG
                            src={require('~images/icons/svg/play.svg')}
                            title='Play'
                            description='Resume music'
                            role='button'
                            aria-disabled={
                                musicContext.currentPlayingSong == undefined
                            }
                            onClick={musicContext.playPause}
                            className={`
                                icon icon-button 
                                ${
                                    musicContext.currentPlayingSong == undefined
                                        ? style.disabled
                                        : ''
                                }
                            `}
                        />
                    ) : (
                        <SVG
                            src={require('~images/icons/svg/pause.svg')}
                            title='Pause'
                            description='Pause music'
                            role='button'
                            onClick={musicContext.playPause}
                            aria-disabled={
                                musicContext.currentPlayingSong == undefined
                            }
                            className={`
                                icon icon-button 
                                ${
                                    musicContext.currentPlayingSong == undefined
                                        ? style.disabled
                                        : ''
                                }
                            `}
                        />
                    )}
                </div>
                <div className={style.nextSong}>
                    <SVG
                        src={require('~images/icons/svg/next-track.svg')}
                        title='Next'
                        description='Play next song'
                        role='button'
                        aria-disabled={
                            musicContext.songQueueIndex ==
                            musicContext.songQueue.length - 1
                        }
                        onClick={() => {
                            musicContext.playNext();
                        }}
                        className={`
                            icon icon-button 
                            ${
                                musicContext.songQueueIndex ==
                                musicContext.songQueue.length - 1
                                    ? style.disabled
                                    : ''
                            }
                        `}
                    />
                </div>
            </div>

            <div className={style.secondaryControls}>
                <div
                    className={`${style.rating} ${
                        ratingToggle ? style.active : null
                    }`}
                >
                    <SVG
                        src={require('~images/icons/svg/star-full.svg')}
                        title='Show ratings'
                        role='button'
                        onClick={() => {
                            handleRatingToggle();
                        }}
                        className='icon icon-button'
                    />
                </div>

                <div className={style.shuffle}>
                    <SVG
                        src={require('~images/icons/svg/shuffle.svg')}
                        title='Shuffle'
                        description='Shuffle queued songs'
                        role='button'
                        onClick={() => {
                            // TODO: shuffle;
                        }}
                        className='icon icon-button'
                    />
                </div>

                <div className={style.repeat}>
                    <SVG
                        src={require('~images/icons/svg/repeat.svg')}
                        title='Repeat'
                        description='Repeat the current song'
                        onClick={() => {
                            // TODO: repeat;
                        }}
                        className='icon icon-button'
                    />
                </div>

                <div className={style.moreOptions}>
                    <SVG
                        src={require('~images/icons/svg/more-options-hori.svg')}
                        title='More options'
                        role='button'
                        onClick={() => {
                            // TODO: open more options menu;
                        }}
                        className='icon icon-button'
                    />
                </div>
            </div>

            <div className={style.volumeSlide}>
                <SVG
                    src={require('~images/icons/svg/volume-up.svg')}
                    title='Mute'
                    description='Mute the music'
                    role='button'
                    onClick={() => {
                        musicContext.setVolume(0); //TODO: Unmute? Store old volume level?
                    }}
                    className='icon icon-button'
                />
                <Slider
                    name='volume'
                    onChange={(_, value: number) => {
                        musicContext.setVolume(value);
                    }}
                    max={100}
                    min={0}
                    value={musicContext.volume}
                />
            </div>
        </div>
    );
};

export default MusicControl;