import React, { useState } from 'react';
import SVG from 'react-inlinesvg';
import { Link } from 'react-router-dom';
import { Album } from '~logic/Album';
import SimpleRating from '~components/SimpleRating';

import style from './index.styl';

interface AlbumDisplayProps {
    album: Album;
    playSongFromAlbum: (albumID: string, random: boolean) => void;
    className?: string;
}

const AlbumDisplay = (props: AlbumDisplayProps) => {
    const [optionsVisible, setOptionsVisible] = useState(false);
    const album = props.album;
    if (!album) return null;
    return (
        <div
            className={`card ${style.albumDisplay} ${props.className}`}
            onMouseOver={() => setOptionsVisible(true)}
            onMouseLeave={() => setOptionsVisible(false)}
        >
            <div className={style.imageContainer}>
                <img src={album.art} alt='Album cover' />
                <div
                    className={`${style.albumActions}`}
                    onClick={(e) => e.preventDefault()}
                >
                    {optionsVisible && (
                        <>
                            <Link
                                to={`/album/${album.id}`}
                                className={`${style.action} ${style.viewAlbum}`}
                            >
                                View album
                            </Link>
                            <span
                                onClick={() => {
                                    props.playSongFromAlbum(album.id, false);
                                }}
                                className={style.action}
                            >
                                <SVG
                                    className='icon icon-inline'
                                    src={require('~images/icons/svg/play.svg')}
                                />
                                Play
                            </span>
                            <span className={style.action}>
                                <SVG
                                    className='icon icon-inline'
                                    src={require('~images/icons/svg/play-next.svg')}
                                />
                                Play next
                            </span>
                            <span className={style.action}>
                                <SVG
                                    className='icon icon-inline'
                                    src={require('~images/icons/svg/play-last.svg')}
                                />
                                Add to queue
                            </span>
                            <span className={style.action}>
                                <SVG
                                    className='icon icon-inline'
                                    src={require('~images/icons/svg/more-options-hori.svg')}
                                />
                                More options
                            </span>
                        </>
                    )}
                </div>
            </div>
            <div className={style.rating} onClick={(e) => e.preventDefault()}>
                <SimpleRating
                    value={album.rating}
                    fav={album.flag}
                    itemID={album.id}
                    type='album'
                />
            </div>
            <div className={style.details}>
                <div className={style.albumInfo}>
                    <Link
                        to={`/album/${album.id}`}
                        className={`card-title ${style.albumName}`}
                    >
                        {album.name}
                    </Link>
                    <Link
                        to={`/artist/${album.artist.id}`}
                        className={style.albumArtist}
                    >
                        {album.artist.name}
                    </Link>
                    <div className={style.albumMeta}>
                        {album.year} - {album.tracks} tracks
                    </div>
                </div>
            </div>
        </div>
    );
};
export default AlbumDisplay;
