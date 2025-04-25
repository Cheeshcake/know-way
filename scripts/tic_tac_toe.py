#!/usr/bin/env python3
# Tic Tac Toe AI using Minimax and Alpha-Beta Pruning
import json
import sys
import random

# Board is represented as a list of 9 strings:
# [0, 1, 2]
# [3, 4, 5]
# [6, 7, 8]

class TicTacToe:
    def __init__(self):
        self.board = [''] * 9
        self.human = 'X'
        self.ai = 'O'
        
    def available_moves(self):
        """Returns a list of available moves"""
        return [i for i, spot in enumerate(self.board) if spot == '']
    
    def make_move(self, position, player):
        """Makes a move on the board"""
        if self.board[position] == '':
            self.board[position] = player
            return True
        return False
    
    def undo_move(self, position):
        """Undo a move on the board"""
        self.board[position] = ''
    
    def check_winner(self, player):
        """Check if the given player has won"""
        win_positions = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8],  # Rows
            [0, 3, 6], [1, 4, 7], [2, 5, 8],  # Columns
            [0, 4, 8], [2, 4, 6]              # Diagonals
        ]
        
        for positions in win_positions:
            if all(self.board[pos] == player for pos in positions):
                return True
        return False
    
    def is_board_full(self):
        """Check if the board is full"""
        return '' not in self.board
    
    def is_game_over(self):
        """Check if the game is over"""
        return self.check_winner(self.human) or self.check_winner(self.ai) or self.is_board_full()
    
    def evaluate(self):
        """Evaluate the board state:
           10 if AI wins, -10 if human wins, 0 for a draw"""
        if self.check_winner(self.ai):
            return 10
        elif self.check_winner(self.human):
            return -10
        return 0
    
    def minimax(self, depth, is_maximizing):
        """Minimax algorithm for Tic Tac Toe"""
        if self.check_winner(self.ai):
            return 10 - depth
        elif self.check_winner(self.human):
            return depth - 10
        elif self.is_board_full():
            return 0
        
        if is_maximizing:
            best_score = float('-inf')
            for move in self.available_moves():
                self.make_move(move, self.ai)
                score = self.minimax(depth + 1, False)
                self.undo_move(move)
                best_score = max(score, best_score)
            return best_score
        else:
            best_score = float('inf')
            for move in self.available_moves():
                self.make_move(move, self.human)
                score = self.minimax(depth + 1, True)
                self.undo_move(move)
                best_score = min(score, best_score)
            return best_score
    
    def alpha_beta(self, depth, alpha, beta, is_maximizing):
        """Alpha-Beta pruning algorithm for Tic Tac Toe"""
        if self.check_winner(self.ai):
            return 10 - depth
        elif self.check_winner(self.human):
            return depth - 10
        elif self.is_board_full():
            return 0
        
        if is_maximizing:
            best_score = float('-inf')
            for move in self.available_moves():
                self.make_move(move, self.ai)
                score = self.alpha_beta(depth + 1, alpha, beta, False)
                self.undo_move(move)
                best_score = max(score, best_score)
                alpha = max(alpha, best_score)
                if beta <= alpha:
                    break
            return best_score
        else:
            best_score = float('inf')
            for move in self.available_moves():
                self.make_move(move, self.human)
                score = self.alpha_beta(depth + 1, alpha, beta, True)
                self.undo_move(move)
                best_score = min(score, best_score)
                beta = min(beta, best_score)
                if beta <= alpha:
                    break
            return best_score
    
    def get_best_move(self, difficulty='hard'):
        """Returns the best move for the AI"""
        if not self.available_moves():
            return -1
        
        # For easy mode, sometimes make a random move
        if difficulty == 'easy' and random.random() < 0.4:
            return random.choice(self.available_moves())
        
        best_score = float('-inf')
        best_move = -1
        
        for move in self.available_moves():
            self.make_move(move, self.ai)
            
            if difficulty == 'hard':
                # Use Alpha-Beta pruning
                score = self.alpha_beta(0, float('-inf'), float('inf'), False)
            else:
                # Use Minimax
                score = self.minimax(0, False)
                
            self.undo_move(move)
            
            if score > best_score:
                best_score = score
                best_move = move
        
        return best_move

def handle_request(data):
    """Handle API request"""
    try:
        game = TicTacToe()
        game.board = data.get('board', [''] * 9)
        difficulty = data.get('difficulty', 'hard')
        
        if 'make_move' in data:
            position = data['make_move']
            if 0 <= position < 9 and game.board[position] == '':
                game.make_move(position, game.human)
                
                # Check if game is over after human move
                if game.check_winner(game.human):
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'human'
                    }
                elif game.is_board_full():
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'draw'
                    }
                
                # AI makes a move
                ai_move = game.get_best_move(difficulty)
                if ai_move >= 0:
                    game.make_move(ai_move, game.ai)
                    
                    # Check if game is over after AI move
                    if game.check_winner(game.ai):
                        return {
                            'board': game.board,
                            'game_over': True,
                            'winner': 'ai',
                            'ai_move': ai_move
                        }
                    elif game.is_board_full():
                        return {
                            'board': game.board,
                            'game_over': True,
                            'winner': 'draw',
                            'ai_move': ai_move
                        }
                    else:
                        return {
                            'board': game.board,
                            'game_over': False,
                            'ai_move': ai_move
                        }
        
        # Just get AI move without making a human move first
        if 'get_ai_move' in data:
            ai_move = game.get_best_move(difficulty)
            if ai_move >= 0:
                game.make_move(ai_move, game.ai)
                
                if game.check_winner(game.ai):
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'ai',
                        'ai_move': ai_move
                    }
                elif game.is_board_full():
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'draw',
                        'ai_move': ai_move
                    }
                else:
                    return {
                        'board': game.board,
                        'game_over': False,
                        'ai_move': ai_move
                    }
        
        return {
            'board': game.board,
            'game_over': game.is_game_over()
        }
    
    except Exception as e:
        return {
            'error': str(e)
        }

if __name__ == "__main__":
    try:
        # Read input data from stdin
        data = json.loads(sys.stdin.read())
        result = handle_request(data)
        # Output result as JSON
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({'error': str(e)})) 